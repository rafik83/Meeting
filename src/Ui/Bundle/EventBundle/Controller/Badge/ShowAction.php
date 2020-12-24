<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Badge;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Badge\AccessToBadgeDeniedException;
use Proximum\Vimeet\Application\Query\Badge\GetUserBadgeByEventQuery;
use Proximum\Vimeet\Domain\Badge\AvailableChecker;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\User\Sheet\HasAccessToSheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class ShowAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var AvailableChecker */
    private $availableChecker;

    /** @var HasAccessToSheet */
    private $hasAccessToSheet;

    public function __construct(
        AvailableChecker $availableChecker,
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        HasAccessToSheet $hasAccessToSheet,
        EngineInterface $engine,
        QueryBusInterface $queryBus
    ) {
        $this->availableChecker = $availableChecker;
        $this->authorizationChecker = $authorizationChecker;
        $this->engine = $engine;
        $this->queryBus = $queryBus;
        $this->hasAccessToSheet = $hasAccessToSheet;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        User $user,
        Sheet $sheet
    ): Response {
        if (!$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)
            || !$this->availableChecker->isSatisfiedBy($sheet)
            || !$this->hasAccessToSheet->isSatisfiedBy($user, $eventDomain->getEvent(), $sheet)
        ) {
            throw new AccessDeniedException();
        }

        $event = $eventDomain->getEvent();

        try {
            return new Response(
                $this->engine->render(
                    'EventBundle:Badge:show.html.twig',
                    [
                        'event' => $event,
                        'sheet' => $sheet,
                        'userBadgeByEventView' => $this->queryBus->handle(
                            new GetUserBadgeByEventQuery($event, $user)
                        ),
                    ]
                )
            );
        } catch (AccessToBadgeDeniedException $exception) {
            throw new AccessDeniedException();
        }
    }
}
