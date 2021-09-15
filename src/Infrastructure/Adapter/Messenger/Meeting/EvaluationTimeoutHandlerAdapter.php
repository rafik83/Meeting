<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\Messenger\Meeting;

use Proximum\Vimeet\Application\Command\Meeting\EvaluationTimeoutHandler;
use Proximum\Vimeet\Application\Command\Meeting\EvaluationTimeoutMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class EvaluationTimeoutHandlerAdapter implements MessageHandlerInterface
{
    private EvaluationTimeoutHandler $evaluationTimeoutHandler;

    public function __construct(EvaluationTimeoutHandler $evaluationTimeoutHandler)
    {
        $this->evaluationTimeoutHandler = $evaluationTimeoutHandler;
    }

    public function __invoke(EvaluationTimeoutMessage $message)
    {
        $this->evaluationTimeoutHandler->handle($message);
    }
}
