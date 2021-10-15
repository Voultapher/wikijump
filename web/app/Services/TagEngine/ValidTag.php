<?php
declare(strict_types=1);

namespace Wikijump\Services\TagEngine;


class ValidTag
{
    // Set of role IDs
    public ?array $required_roles = null;

    // What dates this tag can be applied in
    // [start_date, end_date]
    public ?array $dates = null;

    public function canApplyTagNow(): bool
    {
        // TODO
        return true;
    }
}
