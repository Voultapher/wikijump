<?php
declare(strict_types = 1);

namespace Wikijump\Services\TagEngine;


class TagCondition
{
    // Enum TagConditionType
    public string $condition_type;

    // Tag or tag group name
    public string $name;

    // If TAG_GROUP_OTHER
    public ?TagConditionRequirement $requirement;
}
