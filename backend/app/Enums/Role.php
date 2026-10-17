<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Programmer = 'programmer';
    case Operator = 'operator';
    case QualityManager = 'quality_manager';

    public const PERMISSION_ALL = '*';
    public const ACTIVATE_ORDER = 'order.activate';
    public const INTERRUPT_ORDER = 'order.interrupt';
    public const START_MACHINE = 'machine.start';
    public const STOP_MACHINE = 'machine.stop';
    public const CHECK_QUALITY = 'quality.check';

    /** @return array<int, string> */
    public function permissions(): array
    {
        return match ($this) {
            self::Admin => [self::PERMISSION_ALL],
            self::Programmer => [self::ACTIVATE_ORDER, self::INTERRUPT_ORDER],
            self::Operator => [self::START_MACHINE, self::STOP_MACHINE, self::CHECK_QUALITY],
            self::QualityManager => [self::CHECK_QUALITY, self::STOP_MACHINE],
        };
    }

    public function allows(string $permission): bool
    {
        $permissions = $this->permissions();

        return in_array(self::PERMISSION_ALL, $permissions, true) || in_array($permission, $permissions, true);
    }
}
