<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event\FastCheckin;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

class CheckinActionsAction
{
    /** @var EngineInterface */
    private $engine;

    /** @var RouterInterface */
    private $router;

    public function __construct(EngineInterface $engine, RouterInterface $router)
    {
        $this->engine = $engine;
        $this->router = $router;
    }

    public function __invoke(Event $event, User $user)
    {
        $badgeUrl = $this->router->generate(
            'admin_user_event_badge',
            [
                'user' => $user->getId(),
                'event' => $event->getId(),
            ]
        );

        return $this->engine->renderResponse('@Admin/Event/checkinUser.html.twig', ['badgeUrl' => $badgeUrl]);
    }
}
