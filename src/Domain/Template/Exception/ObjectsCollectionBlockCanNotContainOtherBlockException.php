<?php

namespace Proximum\Vimeet\Domain\Template\Exception;

class ObjectsCollectionBlockCanNotContainOtherBlockException extends TemplateException
{
    protected $message = 'Objects collection block can not contain other block';
}
