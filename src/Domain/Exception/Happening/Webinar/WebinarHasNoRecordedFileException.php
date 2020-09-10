<?php

namespace Proximum\Vimeet\Domain\Exception\Happening\Webinar;

class WebinarHasNoRecordedFileException extends WebinarException
{
    protected $message = 'This webinar has no recorded file';
}
