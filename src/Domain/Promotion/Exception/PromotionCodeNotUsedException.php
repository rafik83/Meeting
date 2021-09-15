<?php

namespace Proximum\Vimeet\Domain\Promotion\Exception;

class PromotionCodeNotUsedException extends PromotionCodeException
{
    /**
     * {@inheritdoc}
     */
    public function getFlash()
    {
        return parent::getFlash() . '.notUsed';
    }
}
