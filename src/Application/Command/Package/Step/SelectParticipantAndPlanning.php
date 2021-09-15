<?php

namespace Proximum\Vimeet\Application\Command\Package\Step;

use Proximum\Vimeet\Domain\Model\Product;

class SelectParticipantAndPlanning extends AbstractStep
{
    /** @var OptionRow */
    public $planningQuantity;

    /** @var array of participantId => Product */
    public $participantsProduct = [];

    public function getPlanningQuantity(): OptionRow
    {
        return $this->planningQuantity;
    }

    public function setPlanningQuantity(OptionRow $planningQuantity): void
    {
        $this->planningQuantity = $planningQuantity;
    }

    public function __get(int $participantId): ?Product
    {
        return $this->participantsProduct[$participantId];
    }

    public function __set(int $participantId, ?Product $participantProduct = null)
    {
        $this->participantsProduct[$participantId] = $participantProduct;
    }
}
