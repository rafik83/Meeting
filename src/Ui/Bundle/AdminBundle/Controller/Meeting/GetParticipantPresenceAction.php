<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Meeting;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Meeting\ParticipantPresenceQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GetParticipantPresenceAction
{
    /** @var QueryBusInterface */
    private $queryBus;

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    public function __construct(
        QueryBusInterface $queryBus,
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter
    ) {
        $this->queryBus = $queryBus;
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
    }

    public function __invoke(Request $request, Event $event, MeetingSlot $meetingSlot): JsonResponse
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $event !== $meetingSlot->getEvent()
        ) {
            throw new AccessDeniedException('Access denied');
        }

        return new JsonResponse(
            $this->queryBus->handle(new ParticipantPresenceQuery($meetingSlot))
        );
    }
}
