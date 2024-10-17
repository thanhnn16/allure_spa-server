<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\Validator;

class UsersImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use Importable;

    private $rows = 0;
    private $successCount = 0;
    private $failures = [];

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        ++$this->rows;

        if ($this->validateRow($row)) {
            $user = new User([
                'full_name' => $row['full_name'],
                'phone_number' => $row['phone_number'] ?? null,
                'email' => $row['email'] ?? null,
                'gender' => $row['gender'] ?? 'other',
                'date_of_birth' => $row['date_of_birth'] ?? null,
                'password' => bcrypt('allurespa'), // Mật khẩu mặc định
                'role' => 'user',
            ]);

            $user->save();
            ++$this->successCount;
            return $user;
        }

        return null;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:255|unique:users,phone_number',
            'email' => 'nullable|email|max:255|unique:users,email',
            'gender' => 'nullable|in:male,female,other',
            'date_of_birth' => 'nullable|date',
        ];
    }

    private function validateRow(array $row)
    {
        $validator = Validator::make($row, $this->rules());

        if ($validator->fails()) {
            $this->onFailure(new Failure(
                $this->rows,
                'Validation',
                $validator->errors()->all(),
                $row
            ));
            return false;
        }

        return true;
    }

    public function onFailure(Failure ...$failures)
    {
        $this->failures = array_merge($this->failures, $failures);
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getFailureCount(): int
    {
        return count($this->failures);
    }

    public function failures(): array
    {
        return $this->failures;
    }
}
