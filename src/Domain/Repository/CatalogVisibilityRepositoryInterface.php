<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Catalog\External\CatalogVisibility;
use Proximum\Vimeet\Domain\Model\Event;

interface CatalogVisibilityRepositoryInterface
{
    /**
     * @param CatalogVisibility $catalogVisibility
     */
    public function add(CatalogVisibility $catalogVisibility);

    /**
     * @param CatalogVisibility $catalogVisibility
     */
    public function set(CatalogVisibility $catalogVisibility);

    /**
     * @param Event $event
     *
     * @return CatalogVisibility|null
     */
    public function getByEvent(Event $event);
}
