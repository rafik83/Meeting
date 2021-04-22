<?php

namespace Proximum\Vimeet\Application\Command\Meeting;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\User;

class RefuseRequest implements Command
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
