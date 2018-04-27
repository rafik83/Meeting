<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
