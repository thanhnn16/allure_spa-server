<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Enums\AuthErrorCode;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;
use App\Models\LoginHistory;
use Torann\GeoIP\Facades\GeoIP;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class AuthService
{
    protected $fcmTokenService;
    protected $emailVerificationService;
    protected $firebaseAuthService;

    public function __construct(
        FcmTokenService $fcmTokenService,
        EmailVerificationService $emailVerificationService,
        FirebaseAuthService $firebaseAuthService
    ) {
        $this->fcmTokenService = $fcmTokenService;
        $this->emailVerificationService = $emailVerificationService;
        $this->firebaseAuthService = $firebaseAuthService;
    }

    public function login(array $credentials)
    {
        $validator = Validator::make($credentials, [
            'phone_number' => 'required|string|regex:/^[0-9]{10}$/',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('phone_number')) {
                throw new \Exception(AuthErrorCode::INVALID_PHONE_FORMAT->value);
            }
            if ($errors->has('password')) {
                throw new \Exception(AuthErrorCode::INVALID_PASSWORD_FORMAT->value);
            }
            throw new \Exception(AuthErrorCode::VALIDATION_ERROR->value);
        }

        $user = User::where('phone_number', $credentials['phone_number'])->first();

        if (!$user) {
            // Ghi lại lịch sử đăng nhập thất bại
            $this->logLoginAttempt([
                'phone_number' => $credentials['phone_number'],
                'status' => 'failed',
                'reason' => AuthErrorCode::USER_NOT_FOUND->value
            ]);
            throw new \Exception(AuthErrorCode::USER_NOT_FOUND->value);
        }

        if (!Auth::attempt($credentials)) {
            // Ghi lại lịch sử đăng nhập thất bại
            $this->logLoginAttempt([
                'user_id' => $user->id,
                'status' => 'failed',
                'reason' => AuthErrorCode::WRONG_PASSWORD->value
            ]);
            throw new \Exception(AuthErrorCode::WRONG_PASSWORD->value);
        }

        // Tạo token cho API login
        $token = $user->createToken('auth_token')->plainTextToken;

        // Ghi lại lịch sử đăng nhập thành công
        $this->logLoginAttempt([
            'user_id' => $user->id,
            'status' => 'success',
            'is_api' => true // Thêm flag để đánh dấu đây là API login
        ]);

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function register(array $data)
    {
        $validator = Validator::make($data, [
            'full_name' => 'required|string|max:255|regex:/^[\pL\s\-]+$/u',
            'phone_number' => 'required|string|regex:/^[0-9]{10}$/|unique:users,phone_number',
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'email' => 'nullable|email|unique:users,email'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('full_name')) {
                throw new \Exception(AuthErrorCode::INVALID_NAME_FORMAT->value);
            }
            if ($errors->has('phone_number')) {
                if (User::where('phone_number', $data['phone_number'])->exists()) {
                    throw new \Exception(AuthErrorCode::PHONE_ALREADY_EXISTS->value);
                }
                throw new \Exception(AuthErrorCode::INVALID_PHONE_FORMAT->value);
            }
            if ($errors->has('password')) {
                throw new \Exception(AuthErrorCode::INVALID_PASSWORD_FORMAT->value);
            }
            if ($errors->has('password_confirmation')) {
                throw new \Exception(AuthErrorCode::PASSWORDS_NOT_MATCH->value);
            }
            if ($errors->has('email')) {
                if (User::where('email', $data['email'])->exists()) {
                    throw new \Exception(AuthErrorCode::EMAIL_ALREADY_EXISTS->value);
                }
                throw new \Exception(AuthErrorCode::INVALID_EMAIL_FORMAT->value);
            }
            throw new \Exception(AuthErrorCode::VALIDATION_ERROR->value);
        }

        $user = User::create([
            'id' => Str::uuid(),
            'full_name' => $data['full_name'],
            'phone_number' => $data['phone_number'],
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Store FCM token for authenticated user
     */
    public function storeFcmToken(string $userId, string $token, string $deviceType): array
    {
        try {
            // Validate device type
            if (!in_array($deviceType, ['android', 'ios', 'web'])) {
                throw new \Exception('Invalid device type');
            }

            $fcmToken = $this->fcmTokenService->storeFcmToken(
                $userId,
                $token,
                $deviceType
            );

            return [
                'data' => $fcmToken,
                'message' => 'FCM token stored successfully',
                'status_code' => 200
            ];
        } catch (\Exception $e) {
            Log::error('Error storing FCM token: ' . $e->getMessage(), [
                'user_id' => $userId,
                'device_type' => $deviceType
            ]);
            throw new \Exception(AuthErrorCode::SERVER_ERROR->value);
        }
    }

    public function changePassword(User $user, array $data)
    {
        $validator = Validator::make($data, [
            'current_password' => 'required|string',
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('current_password')) {
                throw new \Exception(AuthErrorCode::INVALID_PASSWORD_FORMAT->value);
            }
            if ($errors->has('new_password')) {
                throw new \Exception(AuthErrorCode::INVALID_PASSWORD_FORMAT->value);
            }
            throw new \Exception(AuthErrorCode::VALIDATION_ERROR->value);
        }

        // Verify current password
        if (!Hash::check($data['current_password'], $user->password)) {
            throw new \Exception(AuthErrorCode::WRONG_PASSWORD->value);
        }

        // Update password
        $user->password = Hash::make($data['new_password']);
        $user->save();

        return [
            'message' => 'Password changed successfully'
        ];
    }

    public function verifyEmail(string $token, string $lang = 'vi'): array
    {
        $result = $this->emailVerificationService->verifyEmail($token);

        if (!$result['success']) {
            throw new \Exception(AuthErrorCode::INVALID_TOKEN->value);
        }

        return [
            'user' => $result['user'],
            'message' => 'Email verified successfully'
        ];
    }

    public function verifyPhone(string $verificationId, string $code): array
    {
        $result = $this->firebaseAuthService->verifyPhoneNumber(
            $verificationId,
            $code
        );

        if (!$result['success']) {
            throw new \Exception(AuthErrorCode::INVALID_VERIFICATION_CODE->value);
        }

        return [
            'message' => 'Phone number verified successfully'
        ];
    }

    public function resendVerification(User $user, string $type, string $lang = 'vi'): array
    {
        if ($type === 'email') {
            if (!$user->email) {
                throw new \Exception(AuthErrorCode::EMAIL_NOT_FOUND->value);
            }

            if ($user->email_verified_at) {
                throw new \Exception(AuthErrorCode::EMAIL_ALREADY_VERIFIED->value);
            }

            $token = $this->emailVerificationService->sendVerificationEmail($user, $lang);
            return [
                'message' => 'Verification email sent',
                'token' => $token->token
            ];
        }

        if ($type === 'phone') {
            if (!$user->phone_number) {
                throw new \Exception(AuthErrorCode::PHONE_NOT_FOUND->value);
            }

            if ($user->phone_verified_at) {
                throw new \Exception(AuthErrorCode::PHONE_ALREADY_VERIFIED->value);
            }

            $result = $this->firebaseAuthService->sendVerificationCode($user->phone_number);

            if (!$result['success']) {
                throw new \Exception(AuthErrorCode::VERIFICATION_SEND_FAILED->value);
            }

            return [
                'message' => 'Verification SMS sent',
                'verification_id' => $result['verification_id']
            ];
        }

        throw new \Exception(AuthErrorCode::INVALID_VERIFICATION_TYPE->value);
    }

    /**
     * Ghi lại lịch sử đăng nhập
     */
    private function logLoginAttempt(array $data)
    {
        try {
            $deviceType = $this->detectDeviceType(request()->userAgent());

            // Xác định loại thiết bị dựa trên User-Agent
            if (isset($data['is_api']) && $data['is_api']) {
                // Kiểm tra User-Agent để phân biệt mobile/desktop cho API
                if (preg_match('/(android|iphone|ipad)/i', strtolower(request()->userAgent()))) {
                    $deviceType = 'mobile';
                }
            }

            // Tạo bản ghi mới
            $loginHistory = new LoginHistory([
                'user_id' => $data['user_id'] ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'login_at' => now(),
                'status' => $data['status'],
                'device_type' => $deviceType,
                'location' => $this->getLocationFromIp(request()->ip())
            ]);

            // Lưu trực tiếp vào database
            $loginHistory->save();
        } catch (\Exception $e) {
            Log::error('Error logging login attempt: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Phát hiện loại thiết bị từ User Agent
     */
    private function detectDeviceType($userAgent)
    {
        if (preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent)) {
            return 'mobile';
        }
        if (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
            return 'tablet';
        }
        return 'desktop';
    }

    /**
     * Lấy thông tin vị trí từ IP (có thể tích hợp với service bên thứ 3)
     */
    private function getLocationFromIp($ip)
    {
        try {
            if ($ip === '127.0.0.1' || $ip === 'localhost') {
                return 'Local';
            }

            // Sử dụng try-catch để xử lý lỗi GeoIP
            try {
                $location = geoip()->getLocation($ip);
                return $location->city . ', ' . $location->country;
            } catch (\Exception $e) {
                Log::warning('GeoIP lookup failed: ' . $e->getMessage());
                return 'Unknown';
            }
        } catch (\Exception $e) {
            Log::error('Error getting location from IP: ' . $e->getMessage());
            return 'Unknown';
        }
    }

    public function loginWithZalo(array $zaloData)
    {
        try {
            Log::info('Starting Zalo login process', ['data' => $zaloData]);

            // Lấy thông tin user từ Zalo API
            $zaloProfile = $this->getZaloUserProfile($zaloData['access_token']);
            Log::info('Retrieved Zalo profile', ['profile' => $zaloProfile]);

            if (!isset($zaloProfile['id'])) {
                throw new \Exception('Không thể lấy thông tin người dùng từ Zalo');
            }

            // Tìm user theo zalo_id
            $user = User::where('zalo_id', $zaloProfile['id'])->first();

            DB::beginTransaction();
            try {
                if (!$user) {
                    Log::info('Creating new user for Zalo profile', ['zalo_id' => $zaloProfile['id']]);
                    // Tạo user mới
                    $user = User::create([
                        'id' => Str::uuid(),
                        'full_name' => $zaloProfile['name'],
                        'zalo_id' => $zaloProfile['id'],
                        'provider' => 'zalo',
                        'zalo_access_token' => $zaloData['access_token'],
                        'zalo_refresh_token' => $zaloData['refresh_token'],
                        'zalo_token_expires_at' => now()->addSeconds($zaloData['expires_in']),
                        'refresh_token_expires_at' => now()->addSeconds($zaloData['refresh_token_expires_in']),
                        'gender' => $zaloProfile['gender'] ?? null,
                        'date_of_birth' => isset($zaloProfile['birthday']) ?
                            Carbon::createFromFormat('d/m/Y', $zaloProfile['birthday']) : null
                    ]);
                } else {
                    Log::info('Updating existing user', ['user_id' => $user->id]);
                    // Cập nhật thông tin token
                    $user->update([
                        'zalo_access_token' => $zaloData['access_token'],
                        'zalo_refresh_token' => $zaloData['refresh_token'],
                        'zalo_token_expires_at' => now()->addSeconds($zaloData['expires_in']),
                        'refresh_token_expires_at' => now()->addSeconds($zaloData['refresh_token_expires_in'])
                    ]);
                }

                // Xử lý avatar nếu có
                if (isset($zaloProfile['picture']['data']['url'])) {
                    $this->saveZaloAvatar($user, $zaloProfile['picture']['data']['url']);
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Database transaction failed', ['error' => $e->getMessage()]);
                throw new \Exception('Lỗi khi lưu thông tin người dùng');
            }

            // Tạo token xác thực
            $token = $user->createToken('zalo_auth')->plainTextToken;

            // Ghi log đăng nhập thành công
            $this->logLoginAttempt([
                'user_id' => $user->id,
                'status' => 'success',
                'provider' => 'zalo'
            ]);

            return [
                'user' => $user->fresh(['media']),
                'token' => $token
            ];
        } catch (\Exception $e) {
            Log::error('Zalo login failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Đăng nhập bằng Zalo thất bại: ' . $e->getMessage());
        }
    }

    private function saveZaloAvatar(User $user, string $avatarUrl)
    {
        try {
            // Tải ảnh từ URL
            $imageContent = file_get_contents($avatarUrl);
            if (!$imageContent) {
                throw new \Exception('Không thể tải ảnh từ Zalo');
            }

            // Tạo file tạm thời
            $tempFile = tempnam(sys_get_temp_dir(), 'zalo_avatar_');
            file_put_contents($tempFile, $imageContent);

            // Tạo UploadedFile từ file tạm
            $uploadedFile = new \Illuminate\Http\UploadedFile(
                $tempFile,
                'zalo_avatar.jpg',
                'image/jpeg',
                null,
                true
            );

            // Xóa avatar cũ nếu có
            if ($user->media) {
                app(MediaService::class)->delete($user->media);
            }

            // Upload avatar mới
            $media = app(MediaService::class)->create($user, $uploadedFile, 'image');

            // Cập nhật media_id cho user
            $user->update(['media_id' => $media->id]);

            // Xóa file tạm
            @unlink($tempFile);
        } catch (\Exception $e) {
            Log::error('Error saving Zalo avatar: ' . $e->getMessage());
        }
    }

    private function getZaloUserProfile($accessToken)
    {
        $response = Http::get('https://graph.zalo.me/v2.0/me', [
            'access_token' => $accessToken,
            'fields' => 'id,name,picture,birthday,gender'
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to get Zalo user profile');
        }

        return $response->json();
    }

    /**
     * Kiểm tra số điện thoại có phải là của nhân viên hay không
     *
     * @param string $phoneNumber
     * @return array
     */
    public function checkStaffPhone(string $phoneNumber): array
    {
        try {
            // Chuẩn hóa số điện thoại
            $normalizedPhone = $this->normalizePhoneNumber($phoneNumber);

            $user = User::where('phone_number', $normalizedPhone)
                ->whereIn('role', ['staff', 'admin'])
                ->first();

            if (!$user) {
                return [
                    'is_staff' => false,
                    'user' => null
                ];
            }

            // Tạo token nếu là staff
            $token = $user->createToken('staff_verification')->plainTextToken;

            return [
                'is_staff' => true,
                'user' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'role' => $user->role
                ],
                'token' => $token
            ];
        } catch (\Exception $e) {
            Log::error('Error checking staff phone: ' . $e->getMessage(), [
                'phone_number' => $phoneNumber
            ]);
            throw new \Exception('SERVER_ERROR');
        }
    }

    /**
     * Chuẩn hóa số điện thoại về định dạng bắt đầu bằng số 0
     *
     * @param string $phoneNumber
     * @return string
     */
    private function normalizePhoneNumber(string $phoneNumber): string
    {
        // Loại bỏ khoảng trắng và ký tự đặc biệt
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Nếu số điện thoại bắt đầu bằng +84 hoặc 84
        if (str_starts_with($phone, '84')) {
            $phone = '0' . substr($phone, 2);
        }

        return $phone;
    }
}
