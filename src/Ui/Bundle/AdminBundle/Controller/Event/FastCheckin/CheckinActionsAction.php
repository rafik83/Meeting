<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event\FastCheckin;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class CheckinActionsAction
{
    /** @var EngineInterface */
    private $engine;

    public function __construct(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    public function __invoke(Event $event, User $user)
    {
        return $this->engine->renderResponse(
            '@Admin/Event/checkinUser.html.twig',
            [
                'event' => $event,
                'eventId' => $event->getId(),
                'userId' => $user->getId(),
                'user' => $user,
            ]
        );
    }
}
