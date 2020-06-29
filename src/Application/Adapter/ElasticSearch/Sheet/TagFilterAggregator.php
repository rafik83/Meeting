<?php

namespace Proximum\Vimeet\Application\Adapter\ElasticSearch\Sheet;

use Proximum\Vimeet\Domain\Model\Event;

/**
 * Get the aggregations on nestedTaggedData
 */
interface TagFilterAggregator
{
    public function getAggregationsForTag(
        Event $event,
        string $tag,
        string $locale,
        array $filters,
        array $availableSlotIds = [],
        array $sheetsToExclude = []
    ): array;
}
