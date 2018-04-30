<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Exception\Tip;

class TipNotFoundException extends TipException
{
    public $message = 'flash.admin.tip.not_found';
}
