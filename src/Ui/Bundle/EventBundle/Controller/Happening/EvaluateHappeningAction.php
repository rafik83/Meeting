<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Happening;


use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Happening\EvaluateHappening;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Query\Happening\CanEvaluateHappening;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Happening\EvaluateHappeningType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class EvaluateHappeningAction
{
    /** @var Environment */
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

    private HappeningParticipationRepositoryInterface $happeningParticipationRepository;

    private CanEvaluateHappening $canEvaluateHappening;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        Environment $engine,
        CommandBusInterface $commandBus,
        QueryBusInterface $queryBus,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        HappeningParticipationRepositoryInterface $happeningParticipationRepository,
        CanEvaluateHappening $canEvaluateHappening
    )
    {
        $this->engine = $engine;
        $this->commandBus = $commandBus;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->authorizationChecker = $authorizationChecker;
        $this->queryBus = $queryBus;
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->canEvaluateHappening = $canEvaluateHappening;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        UserDomain $userDomain,
        Sheet $sheet,
        Happening $happening
    ): Response
    {
        $user = $userDomain->getUser();

        if (!$this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationChecker->isGranted(SheetVoter::EDIT, $sheet)
            || !$this->canEvaluateHappening->isSatisfiableBy($happening, $user)
            || $eventDomain->getEvent() !== $sheet->getEvent()
        ) {
            throw new AccessDeniedException();
        }

        $event = $eventDomain->getEvent();
        $happeningParticipation = $this->happeningParticipationRepository->findByHappeningAndUser($happening, $user);

        if (null === $happeningParticipation) {
            throw new AccessDeniedException();
        }

        $evaluation = $happeningParticipation->getEvaluation();
        $evaluateHappening = new EvaluateHappening($event, $sheet, $happening, $user, $evaluation);

        $form = $this->formFactory->create(EvaluateHappeningType::class, $evaluateHappening, []);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
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
            $this->engine->render('@Event/Happening/evaluate-happening.html.twig', [
                'event' => $eventDomain->getEvent(),
                'sheet' => $sheet,
                'happening' => $happening,
                'ratingForm' => $form->createView(),
                'mustEvaluateHappening' => $happening->mustEvaluateHappening(),
            ])
        );
    }
}
