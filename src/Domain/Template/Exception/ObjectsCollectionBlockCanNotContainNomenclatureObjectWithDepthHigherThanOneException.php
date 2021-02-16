<?php

namespace Proximum\Vimeet\Domain\Template\Exception;

class ObjectsCollectionBlockCanNotContainNomenclatureObjectWithDepthHigherThanOneException extends TemplateException
{
    protected $message = 'Objects collection block can not contain nomenclature object with a nomenclature depth higher than one';
}
