<?php
declare(strict_types=1);

namespace Wikijump\Services\TagEngine;


class TagConditionList
{
    public TagConditionRequirement $requirement;

    // List of TagConditions
    public array $conditions;

    public function evaluate(): bool
    {
        // TODO
        return true;
    }
}
