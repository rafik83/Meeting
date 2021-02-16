<?php

namespace Proximum\Vimeet\Domain\Promotion\Exception;

class PromotionCodeException extends \DomainException
{
    /**
     * @return string
     */
    public function getFlash()
    {
        return 'flash.package.promotionCode.error';
    }
}
