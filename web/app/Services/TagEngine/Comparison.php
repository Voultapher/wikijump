<?php
declare(strict_types = 1);

namespace Wikijump\Services\TagEngine;

use TypeError;
use Wikijump\Common\Enum;

final class Comparison extends Enum
{
    const GREATER_THAN_EQUALS = '>=';
    const GREATER_THAN = '>';
    const LESS_THAN_EQUALS = '<=';
    const LESS_THAN = '<';
    const EQUALS = '==';
    const NOT_EQUALS = '!=';

    public static function evaluate(int $x, string $cmp, int $y): boolean
    {
        switch ($cmp) {
            case self::GREATER_THAN:
                return $x > $y;
            case self::GREATER_THAN_EQUALS:
                return $x >= $y;
            case self::LESS_THAN:
                return $x < $y;
            case self::LESS_THAN_EQUALS:
                return $x <= $y;
            case self::EQUALS:
                return $x == $y;
            case self::NOT_EQUALS:
                return $x != $y;
            default:
                throw new TypeError("Invalid comparison enum value: $cmp");
        }
    }
}
