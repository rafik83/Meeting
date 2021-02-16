<?php

namespace Proximum\Vimeet\Domain\Template\Exception;

class ObjectsCollectionBlockCanNotContainForbiddenObjectsException extends TemplateException
{
    protected $message = 'Objects collection block can not contain forbidden object';
}
