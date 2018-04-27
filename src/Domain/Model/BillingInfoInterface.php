<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
     *
     * @deprecated to be rewritten to return a Proximum\Vimeet\Domain\Model\Template\AbstractTemplate object
     */
    public function getBillingTemplate();
}
