<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

interface BillingInfoInterface
{
    /**
     * @return array
     */
    public function getBillingData();

    /**
     * @return array
     */
    public function getBillingTemplate();
}
