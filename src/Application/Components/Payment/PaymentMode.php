<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Payment;

class PaymentMode
{
    const CREDITCARD = 'credit-card';
    const TRANSFER   = 'transfer';
    const CHECK      = 'check';
    const LATER      = 'later';
    const NOPAYMENT  = 'no-payment';

    static public $modes = [
        'Carte bancaire'  => self::CREDITCARD,
        'Virement'        => self::TRANSFER,
        'Chèque'          => self::CHECK,
        'Payer plus tard' => self::LATER,
    ];
}
