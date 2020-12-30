<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class CancelRequest
{
    /**
     * @var Request
     */
    public $request;

    /**
     * @var User
     */
    public $emitter;

    /**
     * @var Sheet
     */
    public $sheet;

    /**
     * CancelRequest constructor.
     *
     * @param Request $request
     * @param User    $emitter
     * @param Sheet   $sheet
     */
    public function __construct(Request $request, User $emitter, Sheet $sheet)
    {
        $this->request = $request;
        $this->emitter = $emitter;
        $this->sheet   = $sheet;
    }
}
