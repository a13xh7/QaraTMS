<?php


namespace App\Enums;

/**
 * User role enumeration
 */
enum UserRole: int
{
    case ADMIN = 1;
    case MANAGER = 2;
    case TESTER = 3;

    /**
     * Get the human-readable label for the role
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrator',
            self::MANAGER => 'Manager',
            self::TESTER => 'Tester',
        };
    }

    /**
     * Get the color class for the role (for UI styling)
     */
    public function color(): string
    {
        return match($this) {
            self::ADMIN => 'danger',
            self::MANAGER => 'warning',
            self::TESTER => 'info',
        };
    }

    /**
     * Get all roles as an array
     */
    public static function toArray(): array
    {
        return [
            self::ADMIN->value => self::ADMIN->label(),
            self::MANAGER->value => self::MANAGER->label(),
            self::TESTER->value => self::TESTER->label(),
        ];
    }

    /**
     * Get roles for select dropdown
     */
    public static function forSelect(): array
    {
        return [
            self::ADMIN->value => self::ADMIN->label(),
            self::MANAGER->value => self::MANAGER->label(),
            self::TESTER->value => self::TESTER->label(),
        ];
    }
}
