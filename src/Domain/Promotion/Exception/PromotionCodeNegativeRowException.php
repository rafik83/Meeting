<?php

namespace Proximum\Vimeet\Domain\Promotion\Exception;

class PromotionCodeNegativeRowException extends PromotionCodeException
{
    /**
     * {@inheritdoc}
     */
    public function getFlash()
    {
        return parent::getFlash() . '.negativeRow';
    }
}
