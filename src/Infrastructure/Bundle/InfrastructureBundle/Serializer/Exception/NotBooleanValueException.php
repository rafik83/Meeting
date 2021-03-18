<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Exception;

class NotBooleanValueException extends SerializerException
{
    protected $message = 'The value given is not of type boolean';
}
