<?php

namespace Proximum\Vimeet\Domain\Promotion\Exception;

class PromotionCodeSoldOutException extends PromotionCodeException
{
    /**
     * {@inheritdoc}
     */
    public function getFlash()
    {
        return parent::getFlash() . '.soldOut';
    }
}
