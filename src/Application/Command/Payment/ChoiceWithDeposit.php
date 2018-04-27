<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Payment;

class ChoiceWithDeposit extends AbstractChoice
{
    /** @var bool */
    public $deposit;
}
