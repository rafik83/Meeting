<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Payment;

use Proximum\Vimeet\Domain\Model\Payment\Payment;

interface PaymentRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return null|Payment
     */
    public function findById($id);
}
