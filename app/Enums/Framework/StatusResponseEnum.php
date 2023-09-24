<?php

declare(strict_types=1);

namespace App\Enums\Framework;

enum StatusResponseEnum: string
{
    case SUCCESS = 'success';
    case ERROR = 'error';
    case WARNING = 'warning';
    case INFO = 'info';
    case DATABASE_ERROR = 'database-error';
    case SESSION_ERROR = 'session-error';
    case ROUTE_ERROR = 'route-error';
    case MAIL_ERROR = 'mail-error';

    public static function values(): array
    {
        return array_map(fn($value) => $value->value, self::cases());
    }

    public static function errors(): array
    {
        return [
            self::ERROR->value,
            self::DATABASE_ERROR->value,
            self::SESSION_ERROR->value,
            self::ROUTE_ERROR->value,
            self::MAIL_ERROR->value
        ];
    }
}
