<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\Messenger\Happening\Webinar\Poll;

use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\PublishDelayedPollVoteNotificationHandler;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\PublishDelayedPollVoteNotificationMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class PublishDelayedPollVoteNotificationHandlerAdapter implements MessageHandlerInterface
{
    private PublishDelayedPollVoteNotificationHandler $delayedPollVoteNotificationHandler;

    public function __construct(PublishDelayedPollVoteNotificationHandler $delayedPollVoteNotificationHandler)
    {
        $this->delayedPollVoteNotificationHandler = $delayedPollVoteNotificationHandler;
    }

    public function __invoke(PublishDelayedPollVoteNotificationMessage $message)
    {
        $this->delayedPollVoteNotificationHandler->handle($message);
    }
}
