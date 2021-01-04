<?php

namespace Proximum\Vimeet\Domain\Catalog;

use Proximum\Vimeet\Domain\Model\NomenclatureItem;

class NomenclaturesItemsView
{
    /** @var NomenclatureItem[] */
    public $nomenclaturesItems;

    /** @var int */
    public $maxDepth;

    /**
     * @var NomenclatureItem[] $nomenclaturesItems
     * @param int              $maxDepth
     */
    public function __construct(array $nomenclaturesItems, int $maxDepth)
    {
        $this->nomenclaturesItems = $nomenclaturesItems;
        $this->maxDepth = $maxDepth;
    }

}
