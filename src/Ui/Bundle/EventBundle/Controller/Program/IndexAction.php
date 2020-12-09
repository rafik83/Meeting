<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Program;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Exception\Happening\HappeningException;
use Proximum\Vimeet\Application\Query\Happening\ProgramViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Application\View\Happening\ProgramView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Participant\ParticipantHelper;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class IndexAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var EngineInterface */
    private $engine;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        QueryBusInterface $queryBus,
        RouterInterface $router,
        EngineInterface $engine
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->queryBus = $queryBus;
        $this->engine = $engine;
        $this->router = $router;
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param UserDomain  $userDomain
     *
     * @return Response|RedirectResponse
     */
    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        UserDomain $userDomain
    ): Response {
        if (!$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_HAPPENING_ACCESS', $eventDomain->getEvent())
        ) {
            throw new AccessDeniedException('Access denied!');
        }

        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();

        try {
            /** @var ProgramView $program */
            $program = $this->queryBus->handle(
                new ProgramViewQuery(
                    $eventDomain->getEvent(),
                    $sheet,
                    $user,
                    $request->getLocale(),
                    null
                )
            );
        } catch (HappeningException $exception) {
            return new RedirectResponse(
                $this->router->generate('event_sheet_default', ['sheet' => $sheet->getId()])
            );
        }

        $isUserAloneParticipant = ParticipantHelper::isUserAloneParticipant($user, $sheet);

        $tipTranslationViewQuery = new TipTranslationViewQuery(
            $sheet,
            $user,
            TipTranslationViewQueryHandler::CONTEXT_PROGRAM,
            $request->getLocale()
        );
        $tipTranslationViews = $this->queryBus->handle($tipTranslationViewQuery);

        return new Response($this->engine->render('EventBundle:Program:index.html.twig', [
            'event' => $event,
            'sheet' => $sheet,
            'program' => $program,
            'isUserAloneParticipant' => $isUserAloneParticipant,
            'tipTranslationViews' => $tipTranslationViews,
        ]));
    }
}
