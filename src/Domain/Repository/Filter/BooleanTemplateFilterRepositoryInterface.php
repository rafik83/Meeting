<?php

namespace Proximum\Vimeet\Domain\Repository\Filter;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Filter\BooleanTemplateFilter;

interface BooleanTemplateFilterRepositoryInterface
{
    /**
     * @param Event $event
     *
     * @return BooleanTemplateFilter[]
     */
    public function getByEvent(Event $event);

    public function getByEventIdAndInformationType(int $eventId, string $informationType): array;

    /**
     * @param Event $event
     */
    public function deleteForEvent(Event $event);

    /**
     * @param BooleanTemplateFilter $booleanTemplateFilter
     */
    public function add(BooleanTemplateFilter $booleanTemplateFilter);
}
