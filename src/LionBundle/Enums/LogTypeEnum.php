<?php

declare(strict_types=1);

namespace Lion\Bundle\Enums;

enum LogTypeEnum: string
{
    case INFO = 'info';
    case NOTICE = 'notice';
    case WARNING = 'warning';
    case ERROR = 'error';
    case CRITICAL = 'critical';
    case ALERT = 'alert';
    case EMERGENCY = 'emergency';
}
