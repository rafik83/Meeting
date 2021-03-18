<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Planner;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Analytic\MeetingSolution\MeetingSolutionListQuery;
use Proximum\Vimeet\Domain\KeyDates\Checker\EventOpenAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\PlannerJobRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class PlannerAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var EngineInterface */
    private $engine;

    /** @var EventOpenAccessChecker */
    private $eventOpenAccessChecker;

    /** @var MeetingPublishedAccessChecker */
    private $meetingPublishedAccessChecker;

    /** @var PlannerJobRepositoryInterface */
    private $plannerJobRepository;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        EngineInterface $engine,
        EventOpenAccessChecker $eventOpenAccessChecker,
        MeetingPublishedAccessChecker $meetingPublishedAccessChecker,
        PlannerJobRepositoryInterface $plannerJobRepository,
        QueryBusInterface $queryBus
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->engine = $engine;
        $this->eventOpenAccessChecker = $eventOpenAccessChecker;
        $this->meetingPublishedAccessChecker = $meetingPublishedAccessChecker;
        $this->plannerJobRepository = $plannerJobRepository;
        $this->queryBus = $queryBus;
    }

    /**
     * @param Event $event
     *
     * @return Response
     */
    public function __invoke(Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('Access denied');
        }

        $lastPlannerJob = $this->plannerJobRepository->findLastByEvent($event);
        $isEventOpened = $this->eventOpenAccessChecker->allowedToAccess($event);
        $canDeleteMeetings = !$isEventOpened && !$this->meetingPublishedAccessChecker->allowedToAccess($event);
        $meetingSolutions = $this->queryBus->handle(new MeetingSolutionListQuery($event));

        return new Response(
            $this->engine->render(
                'AdminBundle:Planner:index.html.twig',
                [
                    'event' => $event,
                    'meetingSolutions' => $meetingSolutions,
                    'lastPlannerJob' => $lastPlannerJob,
                    'isEventOpened' => $isEventOpened,
                    'canDeleteMeetings' => $canDeleteMeetings,
                ]
            )
        );
    }
}
