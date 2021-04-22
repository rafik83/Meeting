<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Meeting;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Meeting\EvaluateMeeting;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Query\Contact\GetContactViewQuery;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\EvaluateMeetingType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class EvaluateMeetingAction
{
    /** @var Environment */
    private $twig;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        Environment $twig,
        CommandBusInterface $commandBus,
        QueryBusInterface $queryBus,
        RouterInterface $router,
        FormFactoryInterface $formFactory
    ) {
        $this->twig = $twig;
        $this->commandBus = $commandBus;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->authorizationChecker = $authorizationChecker;
        $this->queryBus = $queryBus;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Sheet $sheet,
        Meeting $meeting
    ): Response {
        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)
            || !$meeting->hasSheet($sheet)
            || !$meeting->isVisio()
            || $eventDomain->getEvent() !== $sheet->getEvent()
            || !$sheet->getType()->canEvaluateMeeting()
        ) {
            throw new AccessDeniedException();
        }

        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();
        $participant = $sheet->getUserParticipant($user);

        if (!$participant instanceof Participant) {
            $participant = $sheet->getFirstParticipant();
        }

        $evaluateMeeting = new EvaluateMeeting($event, $sheet, $meeting, $user);
        $form = $this->formFactory->create(EvaluateMeetingType::class, $evaluateMeeting, []);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($evaluateMeeting);

            $redirectTo = $request->query->get('redirectTo');

            if ($redirectTo) {
                return new RedirectResponse($redirectTo);
            }

            return new RedirectResponse(
                $this->router->generate(Route::AGENDA_PARTICIPANT, [
                    'sheet' => $sheet->getId(),
                    'participant' => $participant->getId(),
                ])
            );
        }

        $locale = $request->getLocale();
        $contacts = [];

        foreach ($meeting->getMetParticipants($sheet) as $metParticipant) {
            $contacts[] = $this->queryBus->handle(new GetContactViewQuery(
                $event,
                $sheet,
                $participant,
                $metParticipant->getUser(),
                $locale
            ));
        }

        return new Response(
            $this->twig->render('@Event/Meeting/evaluate-meeting.html.twig', [
                'event' => $eventDomain->getEvent(),
                'sheet' => $sheet,
                'participant' => $participant,
                'ratingForm' => $form->createView(),
                'contacts' => $contacts,
                'mustEvaluateMeeting' => $sheet->getType()->mustEvaluateMeeting(),
            ])
        );
    }
}
