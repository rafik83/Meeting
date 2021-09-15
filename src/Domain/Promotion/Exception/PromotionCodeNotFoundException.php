<?php

namespace Proximum\Vimeet\Domain\Promotion\Exception;

class PromotionCodeNotFoundException extends PromotionCodeException
{
    /**
     * {@inheritdoc}
     */
    public function getFlash()
    {
        return parent::getFlash() . '.notFound';
    }
}
