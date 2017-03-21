<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Payment;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Payment\Payment;

interface PaymentRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return null|Payment
     */
    public function findById($id);
    
    /**
     * @param \DateTimeInterface $beginDate
     * @param \DateTimeInterface $endDate
     * @param Event[]            $events
     *
     * @return Payment[]
     */
    public function findPaidByDateRangeAndCrossEvent(
        \DateTimeInterface $beginDate,
        \DateTimeInterface $endDate,
        array $events
    );
}
