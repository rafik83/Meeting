<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Model;

use Proximum\Vimeet\Domain\Model\Template\AbstractTemplate;

interface BillingInfoInterface
{
    /**
     * @return array
     */
    public function getBillingData();

    /**
     * @return AbstractTemplate
     */
    public function getBillingTemplate();
}
