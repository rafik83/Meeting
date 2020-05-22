<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Meeting;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Components\Navigation\Route;
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
use Symfony\Component\Templating\EngineInterface;

class EvaluateMeetingAction
{
    /** @var EngineInterface */
    private $engine;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        EngineInterface $engine,
        CommandBusInterface $commandBus,
        RouterInterface $router,
        FormFactoryInterface $formFactory
    ) {
        $this->engine = $engine;
        $this->commandBus = $commandBus;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->authorizationChecker = $authorizationChecker;
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
        ) {
            throw new AccessDeniedException();
        }

        $form = $this->formFactory->create(EvaluateMeetingType::class, [
            'submit' => true,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $user = $userDomain->getUser();
            $participant = $sheet->getUserParticipant($user);

            if (!$participant instanceof Participant) {
                $participant = $sheet->getFirstParticipant();
            }

            return new RedirectResponse(
                $this->router->generate(Route::AGENDA_PARTICIPANT, [
                    'sheet' => $sheet->getId(),
                    'participant' => $participant->getId(),
                ])
            );
        }

        return new Response(
            $this->engine->render('@Event/Meeting/evaluate-meeting.html.twig', [
                'event' => $eventDomain->getEvent(),
                'sheet' => $sheet,
            ])
        );
    }
}
