<?php

namespace App\Enums;

enum AuthErrorCode: string
{
    case USER_NOT_FOUND = 'USER_NOT_FOUND';
    case WRONG_PASSWORD = 'WRONG_PASSWORD';
    case EMAIL_ALREADY_EXISTS = 'EMAIL_ALREADY_EXISTS';
    case PHONE_ALREADY_EXISTS = 'PHONE_ALREADY_EXISTS';
    case VALIDATION_ERROR = 'VALIDATION_ERROR';
    case MISSING_CONTACT_INFO = 'MISSING_CONTACT_INFO';
    case SERVER_ERROR = 'SERVER_ERROR';
    case UNAUTHORIZED_ACCESS = 'UNAUTHORIZED_ACCESS';
    case INVALID_PHONE_FORMAT = 'INVALID_PHONE_FORMAT';
    case INVALID_PASSWORD_FORMAT = 'INVALID_PASSWORD_FORMAT';
    case INVALID_EMAIL_FORMAT = 'INVALID_EMAIL_FORMAT';
    case INVALID_NAME_FORMAT = 'INVALID_NAME_FORMAT';
    case PASSWORDS_NOT_MATCH = 'PASSWORDS_NOT_MATCH';
    case WRONG_CURRENT_PASSWORD = 'Mật khẩu hiện tại không đúng';
    case PASSWORD_CHANGE_FAILED = 'Không thể thay đổi mật khẩu';
    case INVALID_TOKEN = 'INVALID_TOKEN';
    case INVALID_VERIFICATION_CODE = 'INVALID_VERIFICATION_CODE';
    case EMAIL_NOT_FOUND = 'EMAIL_NOT_FOUND';
    case PHONE_NOT_FOUND = 'PHONE_NOT_FOUND';
    case INVALID_VERIFICATION_TYPE = 'INVALID_VERIFICATION_TYPE';
    case EMAIL_ALREADY_VERIFIED = 'EMAIL_ALREADY_VERIFIED';
    case PHONE_ALREADY_VERIFIED = 'PHONE_ALREADY_VERIFIED';
    case VERIFICATION_SEND_FAILED = 'VERIFICATION_SEND_FAILED';
    case ZALO_LOGIN_FAILED = 'ZALO_LOGIN_FAILED';
}
