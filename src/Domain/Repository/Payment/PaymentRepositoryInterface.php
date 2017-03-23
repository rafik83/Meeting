<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Payment;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Model\Transaction;

interface PaymentRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return null|Payment
     */
    public function findById($id);

    /**
     * @param ArrayCollection|Transaction[]
     *
     * @return ArrayCollection|Payment[]
     */
    public function getByTransactions(array $transactions);
}
