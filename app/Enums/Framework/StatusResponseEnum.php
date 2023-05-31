<?php

namespace App\Enums\Framework;

enum StatusResponseEnum: string {

    case SUCCESS = "success";
    case ERROR = "error";
    case WARNING = "warning";
    case INFO = "info";
    case DATABASE_ERROR = "database-error";
    case SESSION_ERROR = "session-error";
    case ROUTE_ERROR = "route-error";
    case MAIL_ERROR = "mail-error";

    public static function values(): array {
        return array_map(fn($value) => $value->value, self::cases());
    }

}
