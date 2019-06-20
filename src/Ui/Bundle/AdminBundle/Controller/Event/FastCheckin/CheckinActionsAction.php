<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Event\FastCheckin;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CheckinActionsAction
{
    /** @var EngineInterface */
    private $engine;
    /**
     * @var AuthorizationCheckerAdapterInterface
     */
    private $authorizationCheckerAdapter;

    public function __construct(
        EngineInterface $engine,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
    ) {
        $this->engine = $engine;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    public function __invoke(Event $event, User $user)
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

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
