<?php

namespace Proximum\Vimeet\Domain\Promotion\Exception;

class PromotionCodeConflictException extends PromotionCodeException
{
    /**
     * {@inheritdoc}
     */
    public function getFlash()
    {
        return parent::getFlash() . '.codeConflict';
    }
}
