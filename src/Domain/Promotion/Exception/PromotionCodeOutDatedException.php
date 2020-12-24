<?php

namespace Proximum\Vimeet\Domain\Promotion\Exception;

class PromotionCodeOutDatedException extends PromotionCodeException
{
    /**
     * {@inheritdoc}
     */
    public function getFlash()
    {
        return parent::getFlash() . '.outDated';
    }
}
