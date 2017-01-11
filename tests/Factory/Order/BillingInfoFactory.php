<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Factory\Order;


use Proximum\Vimeet\Domain\Model\Address;
use Proximum\Vimeet\Domain\Model\Order\BillingInfo;

class BillingInfoFactory
{
    /**
     * @return BillingInfo
     */
    public static function create()
    {
        return new BillingInfo(
            'man',
            'John',
            'Doe',
            'position',
            'phone',
            'mobile',
            'email@email.fr',
            'company',
            new Address('street', 'zipcode', 'city', 'FR'),
            'vatNumber'
        );
    }
}
