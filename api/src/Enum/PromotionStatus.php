<?php

namespace App\Enum;

enum PromotionStatus: string
{
    case None = 'None';
    case Basic = 'Basic';
    case Pro = 'Pro';

    /**
     * Get all the valid values of the PromotionStatus enum.
     *
     * @return array
     */
    public static function values(): array
    {
        return [
            self::None->value,
            self::Basic->value,
            self::Pro->value,
        ];
    }
}
