<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Exception\Tip;

class NoTipAvailableException extends TipException
{
    /** @var string */
    protected $message = 'flash.admin.tip.no_tip_available';
}
