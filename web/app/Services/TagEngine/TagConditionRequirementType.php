<?php
declare(strict_types = 1);

namespace Wikijump\Services\TagEngine;

use Wikijump\Common\Enum;

final class TagConditionRequirementType extends Enum
{
    const ALL_OF = 'all-of';
    const NONE_OF = 'none-of';
    const CUSTOM = 'custom';
}
