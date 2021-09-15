<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Happening\Poll;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Happening\Webinar\CanAccessToWebinar;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\GetHappeningPollResults;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Happening\ParticipationVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GetHappeningPollResultsAction
{
    private AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter;

    private CanAccessToWebinar $canAccessToWebinar;

    private QueryBusInterface $queryBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CanAccessToWebinar $canAccessToWebinar,
        QueryBusInterface $queryBus
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->canAccessToWebinar = $canAccessToWebinar;
        $this->queryBus = $queryBus;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        Happening $happening,
        Happening\Poll $poll,
        UserDomain $userDomain
    ): JsonResponse {
        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();

        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_HAPPENING_ACCESS', $event)
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
            || !$this->authorizationCheckerAdapter->isGranted(ParticipationVoter::PARTICIPATE, $sheet)
            || !$this->canAccessToWebinar->isSatisfiableBy($poll->getHappening(), $user)
            || $happening !== $poll->getHappening()
            || $poll->getHappening()->getEvent() !== $event
            || $sheet->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied to this happening');
        }

        $pollViews = $this->queryBus->handle(new GetHappeningPollResults($poll));

        return new JsonResponse($pollViews);
    }
}
