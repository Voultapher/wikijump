<?php
declare(strict_types = 1);

namespace Wikijump\Services\TagEngine;

use Wikijump\Common\Enum;

class TagConditionRequirement
{
    // TagConditionRequirement enum
    public string $requirement_type;

    // For type 'other':

    // Comparison enum
    public string $comparison;
    public int $value;

    public function evaluate(): bool
    {
        // TODO
        return true;
    }
}
