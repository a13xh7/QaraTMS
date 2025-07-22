<?php


namespace App\Enums;

/**
 * Test run case status enumeration
 */
enum TestRunCaseStatus: int
{
    case PASSED = 1;
    case FAILED = 2;
    case BLOCKED = 3;
    case SKIPPED = 4;

    /**
     * Get the human-readable label for the status
     */
    public function label(): string
    {
        return match($this) {
            self::PASSED => 'Passed',
            self::FAILED => 'Failed',
            self::BLOCKED => 'Blocked',
            self::SKIPPED => 'Skipped',
        };
    }

    /**
     * Get the color class for the status (for UI styling)
     */
    public function color(): string
    {
        return match($this) {
            self::PASSED => 'success',
            self::FAILED => 'danger',
            self::BLOCKED => 'warning',
            self::SKIPPED => 'secondary',
        };
    }

    /**
     * Get the icon for the status
     */
    public function icon(): string
    {
        return match($this) {
            self::PASSED => 'check-circle',
            self::FAILED => 'times-circle',
            self::BLOCKED => 'exclamation-triangle',
            self::SKIPPED => 'minus-circle',
        };
    }

    /**
     * Get all statuses as an array
     */
    public static function toArray(): array
    {
        return [
            self::PASSED->value => self::PASSED->label(),
            self::FAILED->value => self::FAILED->label(),
            self::BLOCKED->value => self::BLOCKED->label(),
            self::SKIPPED->value => self::SKIPPED->label(),
        ];
    }

    /**
     * Get statuses for select dropdown
     */
    public static function forSelect(): array
    {
        return [
            self::PASSED->value => self::PASSED->label(),
            self::FAILED->value => self::FAILED->label(),
            self::BLOCKED->value => self::BLOCKED->label(),
            self::SKIPPED->value => self::SKIPPED->label(),
        ];
    }
}
