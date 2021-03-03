<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Type\RegistrationPath;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Type\RegistrationPath\AddQuestion;
use Proximum\Vimeet\Application\Query\RegistrationPath\EventRegistrationPathQuery;
use Proximum\Vimeet\Application\Query\RegistrationPath\EventRegistrationPathView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Answer;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\RegistrationPath\AddQuestionType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class AddQuestionAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var Environment */
    private $twig;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag,
        FormFactoryInterface $formFactory,
        Environment $twig,
        QueryBusInterface $queryBus,
        RouterInterface $router
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->queryBus = $queryBus;
        $this->router = $router;
    }

    /**
     * @param Request     $request
     * @param Event       $event
     * @param Answer|null $answer
     *
     * @return Response
     */
    public function __invoke(Request $request, Event $event, ?Answer $answer): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || (null !== $answer && $answer->getEvent() !== $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        if (null === $answer) {
            $this->checkEventHasAlreadyAFirstQuestion($event);
        }

        if (null !== $answer && $answer->hasAlreadyNextStep()) {
            throw new \LogicException('Answer already has a next step');
        }

        $locale = $event->getAvailableLocale($request->getLocale());

        $addQuestion = new AddQuestion($event, $answer);
        $addQuestionForm = $this->formFactory->create(
            AddQuestionType::class,
            $addQuestion,
            ['event' => $event, 'submit' => true]
        );

        $addQuestionForm->handleRequest($request);

        if ($addQuestionForm->isSubmitted() && $addQuestionForm->isValid()) {
            $this->commandBus->handle($addQuestion);

            $this->flashBag->add('success', 'flash.registrationPath.addQuestion.success');

            return new RedirectResponse(
                $this->router->generate('admin_type_registration_path_show', ['event' => $event->getId()])
            );
        }

        return new Response($this->twig->render(
            '@Admin/Type/RegistrationPath/addQuestion.html.twig',
            [
                'event' => $event,
                'questionTitle' => null !== $answer ? $answer->getQuestion()->getTitle($locale) : null,
                'answerTitle' => null !== $answer ? $answer->getTitle($locale) : null,
                'form' => $addQuestionForm->createView(),
            ]
        ));
    }

    private function checkEventHasAlreadyAFirstQuestion(Event $event): void
    {
        /** @var EventRegistrationPathView $eventRegistrationPathView */
        $eventRegistrationPathView = $this->queryBus->handle(
            new EventRegistrationPathQuery($event, $event->getLocaleFallback())
        );

        if ($eventRegistrationPathView->hasQuestion()) {
            throw new AccessDeniedException('Access denied; Registration path has already a first question.');
        }
    }
}
