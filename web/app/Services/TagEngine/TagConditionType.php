<?php
declare(strict_types = 1);

namespace Wikijump\Services\TagEngine;

use Wikijump\Common\Enum;

final class TagConditionType extends Enum
{
    const TAG_IS_PRESENT = 'tag-is-present';
    const TAG_IS_ABSENT = 'tag-is-absent';
    const TAG_GROUP_IS_FULLY_PRESENT = 'tag-group-is-present';
    const TAG_GROUP_IS_FULLY_ABSENT = 'tag-group-is-absent';
    const TAG_GROUP_CUSTOM = 'tag-group-custom';
}
