<?php
declare(strict_types=1);

namespace Wikijump\Services\TagEngine;


class TagConfiguration
{
    // 'tag_name' => ValidTag
    public array $valid_tags = [];

    // 'group_name' => Set['tag_name']
    public array $tag_groups = [];

    // Tag condition lists:
    // 'tag_name' => TagConditionList
    public array $tag_condition_lists = [];

    // Tag group condition lists:
    // 'group_name' => TagConditionList
    public array $tag_group_condition_lists = [];
}
