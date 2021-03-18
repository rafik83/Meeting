<?php

namespace Proximum\Vimeet\Domain\Repository\Catalog;

use Proximum\Vimeet\Domain\Model\Catalog\CatalogTagFilter;
use Proximum\Vimeet\Domain\Model\Event;

interface CatalogTagFilterRepositoryInterface
{
    /**
     * @param Event  $event
     * @param string $type
     *
     * @return CatalogTagFilter[]
     */
    public function getByEventAndType(Event $event, string $type): array;

    public function removeByEventAndType(Event $event, string $type): void;

    public function add(CatalogTagFilter $catalogTagFilter): void;
}
