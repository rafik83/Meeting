<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Type;

use Proximum\Vimeet\Domain\Model\Type\PaymentConditions;

interface PaymentConditionsRepositoryInterface
{
    /**
     * @param PaymentConditions $paymentConditions
     */
    public function add(PaymentConditions $paymentConditions);

    /**
     * @param PaymentConditions $paymentConditions
     */
    public function set(PaymentConditions $paymentConditions);

    /**
     * @param PaymentConditions $paymentConditions
     */
    public function remove(PaymentConditions $paymentConditions);
}
