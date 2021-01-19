<?php

namespace Proximum\Vimeet\Application\Command\Product\Option;

use Proximum\Vimeet\Application\Command\Product\AbstractUpdate;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Product;

class UpdateOption extends AbstractUpdate
{
    /** @var bool */
    public $attributable;

    /** @var Happening[] */
    public $happenings;

    /**
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        parent::__construct($product);

        $this->availabilityCurrent = $product->getAvailabilityCurrent();
        $this->availabilityMax = $product->getAvailabilityMax();
        $this->updatable = $product->isUpdatable();
        $this->deletableUntil = $product->getDeletableUntil();
        $this->buyableUntil = $product->getBuyableUntil();
        $this->subjectedToValidation = $product->isSubjectedToValidation();
        $this->attributable = $product->isAttributable();
        $this->happenings = $product->getHappenings();
        $this->canScanParticipant = $product->canScanParticipant();
    }
}
