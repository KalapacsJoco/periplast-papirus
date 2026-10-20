<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Programmer = 'programmer';
    case Operator = 'operator';
    case QualityManager = 'quality_manager';
    case Coordinator = 'coordinator';

    public const PERMISSION_ALL = '*';

    public const ACTIVATE_ORDER = 'order.activate';

    public const PAUSE_ORDER = 'order.pause';

    public const INTERRUPT_ORDER = 'order.interrupt';

    public const ASSIGN_ORDER = 'order.assign';

    public const START_MACHINE = 'machine.start';

    public const STOP_MACHINE = 'machine.stop';

    public const REPORT_EQUIPMENT_WARNING = 'equipment.report_warning';

    public const CHECK_QUALITY = 'quality.check';

    public const CHECK_COLOR = 'quality.check_color';

    public const ENTER_MEASURED_DATA = 'order.measure';

    /** @return array<int, string> */
    public function permissions(): array
    {
        return match ($this) {
            self::Admin => [self::PERMISSION_ALL],
            self::Programmer => [self::ACTIVATE_ORDER, self::PAUSE_ORDER, self::INTERRUPT_ORDER],
            self::Operator => [self::START_MACHINE, self::STOP_MACHINE, self::REPORT_EQUIPMENT_WARNING, self::CHECK_COLOR, self::ENTER_MEASURED_DATA],
            self::QualityManager => [self::CHECK_QUALITY, self::STOP_MACHINE, self::REPORT_EQUIPMENT_WARNING],
            self::Coordinator => [self::ASSIGN_ORDER, self::START_MACHINE, self::STOP_MACHINE, self::REPORT_EQUIPMENT_WARNING, self::CHECK_QUALITY, self::CHECK_COLOR, self::ENTER_MEASURED_DATA],
        };
    }

    public function allows(string $permission): bool
    {
        $permissions = $this->permissions();

        return in_array(self::PERMISSION_ALL, $permissions, true) || in_array($permission, $permissions, true);
    }
}
