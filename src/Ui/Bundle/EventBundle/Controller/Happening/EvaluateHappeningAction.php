<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Happening;


use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Happening\EvaluateHappening;
use Proximum\Vimeet\Application\Command\Meeting\EvaluateMeeting;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Query\Contact\GetContactViewQuery;
use Proximum\Vimeet\Domain\Model\Happening;
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

class EvaluateHappeningAction
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

    /** @var QueryBusInterface */
    private $queryBus;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        EngineInterface $engine,
        CommandBusInterface $commandBus,
        QueryBusInterface $queryBus,
        RouterInterface $router,
        FormFactoryInterface $formFactory
    ) {
        $this->engine = $engine;
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
        Happening $happening
    ): Response {
        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)
            || !$happening->mustEvaluateHappening()
            || $eventDomain->getEvent() !== $sheet->getEvent()
        ) {
            throw new AccessDeniedException();
        }

        $event = $eventDomain->getEvent();
        $user = $userDomain->getUser();

        $evaluateHappening = new EvaluateHappening($event, $sheet, $happening, $user);
        $form = $this->formFactory->create(EvaluateMeetingType::class, $evaluateHappening, []);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($evaluateHappening);

            $redirectTo = $request->query->get('redirectTo');

            if ($redirectTo) {
                if (substr($redirectTo, 0, 1) !== '/') {
                    throw new \UnexpectedValueException('The url does not start with /');
                }
                return new RedirectResponse($redirectTo);
            }

            return new RedirectResponse(
                $this->router->generate(Route::PROGRAM, [
                    'sheet' => $sheet->getId()
                ])
            );
        }

        return new Response(
            $this->engine->render('@Event/Meeting/evaluate-meeting.html.twig', [
                'event' => $eventDomain->getEvent(),
                'sheet' => $sheet,
                'happening' => $happening,
                'ratingForm' => $form->createView(),
                'mustEvaluateHappening' => $happening->mustEvaluateHappening(),
            ])
        );
    }
}
