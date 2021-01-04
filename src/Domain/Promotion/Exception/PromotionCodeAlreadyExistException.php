<?php

namespace Proximum\Vimeet\Domain\Promotion\Exception;

class PromotionCodeAlreadyExistException extends PromotionCodeException
{
    /**
     * {@inheritdoc}
     */
    public function getFlash()
    {
        return parent::getFlash() . '.alreadyExist';
    }
}
