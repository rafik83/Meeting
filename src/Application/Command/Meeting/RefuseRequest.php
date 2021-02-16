<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\User;

class RefuseRequest
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
     * @var string
     */
    public $message;

    /**
     * @param Request $request
     * @param User    $emitter
     */
    public function __construct(Request $request, User $emitter)
    {
        $this->request = $request;
        $this->emitter = $emitter;
    }
}
