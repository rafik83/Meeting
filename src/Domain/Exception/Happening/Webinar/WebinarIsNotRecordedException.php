<?php

namespace Proximum\Vimeet\Domain\Exception\Happening\Webinar;

class WebinarIsNotRecordedException extends WebinarException
{
    protected $message = 'This webinar has not the recorded option';
}
