<?php

namespace Proximum\Vimeet\Domain\Exception\Happening\Webinar;

class MissingSessionIdException extends WebinarException
{
    protected $message = 'This webinar has no session id';
}
