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
} 