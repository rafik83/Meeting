<?php

namespace Proximum\Vimeet\Application\Query\Agenda\Admin\Request;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Meeting\Request;

class RequestSheetsViewQuery implements Query
{
    /**
     * @var Request
     */
    public $meetingRequest;

    /**
     * @var string
     */
    public $locale;

    /**
     * RequestParticipantViewQuery constructor.
     *
     * @param Request $meetingRequest
     * @param string  $locale
     */
    public function __construct(Request $meetingRequest, $locale)
    {
        $this->meetingRequest = $meetingRequest;
        $this->locale         = $locale;
    }
}
