<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Happening;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\StartWebinarSessionCommand;
use Proximum\Vimeet\Application\Command\Scan\Happening\ScanHappening;
use Proximum\Vimeet\Application\Query\Happening\Webinar\CanAccessToWebinar;
use Proximum\Vimeet\Application\Query\Happening\Webinar\GetWebinarViewQuery;
use Proximum\Vimeet\Application\View\Happening\Webinar\AbstractWebinarView;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Happening\PreviousHappeningEvaluationChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Happening\PreviousHappeningEvaluationCheckerHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\Happening\ParticipationVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class HappeningWebinarAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CanAccessToWebinar */
    private $canAccessToWebinar;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var \DateTimeInterface */
    private $datetime;

    private PreviousHappeningEvaluationCheckerHandler $previousHappeningEvaluationCheckerHandler;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CanAccessToWebinar $canAccessToWebinar,
        CommandBusInterface $commandBus,
        EngineInterface $engine,
        QueryBusInterface $queryBus,
        \DateTimeInterface $datetime,
        PreviousHappeningEvaluationCheckerHandler $previousHappeningEvaluationCheckerHandler
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->canAccessToWebinar = $canAccessToWebinar;
        $this->commandBus = $commandBus;
        $this->engine = $engine;
        $this->queryBus = $queryBus;
        $this->datetime = $datetime;
        $this->previousHappeningEvaluationCheckerHandler = $previousHappeningEvaluationCheckerHandler;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        Happening $happening,
        UserDomain $userDomain
    ): Response {
        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();

        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_HAPPENING_ACCESS', $event)
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
            || !$this->authorizationCheckerAdapter->isGranted(ParticipationVoter::PARTICIPATE, $sheet)
            || !$this->canAccessToWebinar->isSatisfiableBy($happening, $user)
            || $happening->getEvent() !== $event
            || $sheet->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied to this happening');
        }

        $this->commandBus->handle(new StartWebinarSessionCommand($happening));

        $this->commandBus->handle(new ScanHappening($event, $user, $happening, $this->datetime));

        $redirectResponse = ($this->previousHappeningEvaluationCheckerHandler)(
            new PreviousHappeningEvaluationChecker(
                $event,
                $sheet,
                $user,
                $happening,
                $request->getRequestUri(),
            )
        );

        if ($redirectResponse instanceof RedirectResponse) {
            return $redirectResponse;
        }

        /** @var AbstractWebinarView $webinarView */
        $webinarView = $this->queryBus->handle(new GetWebinarViewQuery($happening, $user, $request->getLocale(), $sheet));

        return new Response(
            $this->engine->render(
                $this->getTemplateNameDependingOnContext($webinarView),
                [
                    'event' => $event,
                    'sheet' => $sheet,
                    'userCompleteName' => $user->getAccount()->getCompleteName(),
                    'webinarView' => $webinarView,
                ]
            )
        );
    }

    private function getTemplateNameDependingOnContext(AbstractWebinarView $webinarView): string
    {
        if ($webinarView->isVideoWebinarAndHappeningIsEnded()) {
            return '@Event/Happening/webinar-video.html.twig';
        }

        return $webinarView->isSpeaker
            ? '@Event/Happening/webinar-speaker.html.twig'
            : '@Event/Happening/webinar-viewer.html.twig';
    }
}
