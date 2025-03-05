<?php

namespace App\Enums;

enum EventEnum: int
{
    use EnumToArray;
    case start = 1;
    case startBreak = 2;
    case stopBreak = 3;
    case stop = 4;

    public function label(): string
    {
        return match ($this) {
            self::start => 'start',
            self::startBreak => 'start_break',
            self::stopBreak => 'stop_break',
            self::stop => 'stop'
        };
    }
}
