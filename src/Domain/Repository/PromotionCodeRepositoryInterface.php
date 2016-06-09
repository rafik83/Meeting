<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\PromotionCode;

interface PromotionCodeRepositoryInterface
{
    /**
     * @param PromotionCode $code
     */
    public function add(PromotionCode $code);

    /**
     * @param PromotionCode $code
     */
    public function set(PromotionCode $code);
}
