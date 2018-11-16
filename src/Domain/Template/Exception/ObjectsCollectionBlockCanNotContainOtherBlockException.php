<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template\Exception;

class ObjectsCollectionBlockCanNotContainOtherBlockException extends TemplateException
{
    protected $message = 'Objects collection block can not contain other block';
}
