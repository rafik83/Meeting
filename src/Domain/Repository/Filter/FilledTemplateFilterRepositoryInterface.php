<?php

namespace Proximum\Vimeet\Domain\Repository\Filter;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Filter\FilledTemplateFilter;

interface FilledTemplateFilterRepositoryInterface
{
    public function getByEvent(Event $event): array;

    public function getByEventIdAndInformationType(int $eventId, string $informationType): array;

    public function deleteForEvent(Event $event): void;

    public function add(FilledTemplateFilter $booleanTemplateFilter): void;
}
