<?php

namespace Proximum\Vimeet\Domain\Repository\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet\ImportMapping;

interface ImportMappingRepositoryInterface
{
    public function add(ImportMapping $importMapping): void;
    public function update(ImportMapping $importMapping): void;

    /**
     * @param Event $event
     *
     * @return ImportMapping[]
     */
    public function getByEvent(Event $event): array;

    public function getById($savedImportMappingId): ?ImportMapping;

    public function hasExistingMappingWithTitle(Event $event, string $title): bool;
}
