<?php

namespace App\Enums;

/**
 * Test case priority enumeration
 */
enum CasePriority: int
{
    case NO = 0;
    case LOW = 1;
    case MEDIUM = 2;
    case HIGH = 3;

    /**
     * Get the human-readable label for the priority
     */
    public function label(): string
    {
        return match($this) {
            self::NO => 'No Priority',
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
        };
    }

    /**
     * Get the color class for the priority (for UI styling)
     */
    public function color(): string
    {
        return match($this) {
            self::NO => 'secondary',
            self::LOW => 'info',
            self::MEDIUM => 'warning',
            self::HIGH => 'danger',
        };
    }

    /**
     * Get the icon for the priority
     */
    public function icon(): string
    {
        return match($this) {
            self::NO => 'minus',
            self::LOW => 'arrow-down',
            self::MEDIUM => 'minus',
            self::HIGH => 'arrow-up',
        };
    }

    /**
     * Get all priorities as an array
     */
    public static function toArray(): array
    {
        return [
            self::NO->value => self::NO->label(),
            self::LOW->value => self::LOW->label(),
            self::MEDIUM->value => self::MEDIUM->label(),
            self::HIGH->value => self::HIGH->label(),
        ];
    }

    /**
     * Get priorities for select dropdown
     */
    public static function forSelect(): array
    {
        return [
            self::NO->value => self::NO->label(),
            self::LOW->value => self::LOW->label(),
            self::MEDIUM->value => self::MEDIUM->label(),
            self::HIGH->value => self::HIGH->label(),
        ];
    }
}
