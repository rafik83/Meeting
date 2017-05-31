<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Exception\Tip;

class TipNotAffectOnEventException extends TipException
{
    public $message = 'flash.admin.tip.remove.unauthorized';
}
