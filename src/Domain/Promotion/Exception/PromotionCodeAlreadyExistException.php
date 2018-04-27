<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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
