<?php
declare(strict_types=1);

namespace Wikijump\Services\TagEngine;


class TagConfiguration
{
    /**
     * @var array $tags The rules and definitions concerning tags.
     *
     * Schema: [
     *   'tag_name' => [
     *     'properties' => [
     *        ?'role_ids' => [list of role IDs that can apply this tag],
     *        ?'date_bound' => [start_date, end_date],
     *      ],
     *     'condition_lists' => [
     *       zero or more:
     *       array matching TagConditionList
     *     ],
     *  ],
     * ]
     */
    private array $tags;

    /**
     * @var array $tag_groups The rules and definitions concerning tags.
     *
     * Schema: [
     *   'group_name' => [
     *     'members' => [list of tag names],
     *     'condition_lists' => [
     *       zero or more:
     *       array matching TagConditionList
     *     ],
     *   ],
     * ]
     */
    private array $tag_groups;

    public function __construct(array $data)
    {
        $this->tags = $data->tags ?? [];
        $this->tag_groups = $data->tag_groups ?? [];
    }

    public function toJson(): array
    {
        return [
            'tags' => $this->tags,
            'tag_groups' => $this->tag_groups,
        ];
    }
}
