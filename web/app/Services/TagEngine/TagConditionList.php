<?php
declare(strict_types=1);

namespace Wikijump\Services\TagEngine;


class TagConditionList
{
    /**
     * @var array The definition for a set of conditions to be verified
     *
     * Schema: [
     *   'requires' => 'all-of' | 'one-of' | 'custom',
     *    if 'custom' only:
     *   'compare' => 'gte' | 'gt' | 'lte' | 'lt' | 'eq' | 'neq',
     *   'value' => integer,
     *
     *   'conditions' => [
     *     one or more:
     *     [
     *       'type' => 'tag-is-present' | 'tag-is-absent' | 'tag-group-is-present' | 'tag-group-is-absent' | 'tag-group-custom',
     *       if 'tag-group-custom' only:
     *       'compare' => 'gte' | 'gt' | 'lte' | 'lt' | 'eq' | 'neq',
     *       'value' => integer,
     *
     *       'name' => 'tag or tag group name',
     *     ],
     *   ],
     * ]
     */
    private array $condition_list;

    public function __construct(array $condition_list)
    {
        $this->condition_list = $condition_list;
    }
}
