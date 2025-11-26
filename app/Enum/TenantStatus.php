<?php

namespace App\Enum;

enum TenantStatus: string
{
    case DEACTIVE = 'deactive';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::DEACTIVE => 'Deactive',
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
        };
    }

    public function colors(): array
    {
        return match ($this) {
            self::ACTIVE => [
                'bg' => 'bg-success-50 dark:bg-success-900/10',
                'text' => 'text-success-700 dark:text-success-400',
                'dot' => 'bg-success-500',
                'border' => 'border-success-200 dark:border-success-800',
            ],
            self::DEACTIVE => [
                'bg' => 'bg-gray-50 dark:bg-gray-800/60',
                'text' => 'text-gray-700 dark:text-gray-400',
                'dot' => 'bg-gray-400',
                'border' => 'border-gray-200 dark:border-gray-700',
            ],
            self::SUSPENDED => [
                'bg' => 'bg-warning-50 dark:bg-warning-900/10',
                'text' => 'text-warning-700 dark:text-warning-400',
                'dot' => 'bg-warning-500',
                'border' => 'border-warning-200 dark:border-warning-800',
            ],
        };
    }

    public static function labels(): array
    {
        return array_column(array_map(
            fn ($case) => ['value' => $case->value, 'label' => $case->label()],
            self::cases()
        ), 'label', 'value');
    }

    public static function colorMap(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = $case->colors();
        }

        return $result;
    }

    public static function defaultColors(): array
    {
        return [
            'bg' => 'bg-primary-50 dark:bg-primary-900/10',
            'text' => 'text-primary-700 dark:text-primary-400',
            'dot' => 'bg-primary-500',
            'border' => 'border-primary-200 dark:border-primary-800',
        ];
    }
}
