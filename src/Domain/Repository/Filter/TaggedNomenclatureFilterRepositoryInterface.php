<?php

namespace Proximum\Vimeet\Domain\Repository\Filter;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Filter\TaggedNomenclatureFilter;

interface TaggedNomenclatureFilterRepositoryInterface
{
    /**
     * @param Event    $event
     * @param string[] $tags
     */
    public function deleteForEventAndTags(Event $event, array $tags): void;

    /**
     * @param TaggedNomenclatureFilter $taggedNomenclatureFilter
     */
    public function add(TaggedNomenclatureFilter $taggedNomenclatureFilter);

    /**
     * @param Event  $event
     * @param string $tag
     *
     * @return TaggedNomenclatureFilter|null
     */
    public function getByEventAndTag(Event $event, $tag);

    public function getByEvent(Event $event): array;
}
