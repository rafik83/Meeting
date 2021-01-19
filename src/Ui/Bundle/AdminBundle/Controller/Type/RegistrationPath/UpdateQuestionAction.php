<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Type\RegistrationPath;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Type\RegistrationPath\UpdateQuestion;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Question;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\RegistrationPath\UpdateQuestionType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UpdateQuestionAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var EngineInterface */
    private $engine;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag,
        FormFactoryInterface $formFactory,
        EngineInterface $engine,
        RouterInterface $router
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->formFactory = $formFactory;
        $this->engine = $engine;
        $this->router = $router;
    }

    public function __invoke(Request $request, Event $event, Question $question): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $question->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $updateQuestion = new UpdateQuestion($question);
        $updateQuestionForm = $this->formFactory->create(
            UpdateQuestionType::class,
            $updateQuestion,
            ['event' => $event, 'submit' => true]
        );

        $updateQuestionForm->handleRequest($request);

        if ($updateQuestionForm->isSubmitted() && $updateQuestionForm->isValid()) {
            $this->commandBus->handle($updateQuestion);

            $this->flashBag->add('success', 'flash.registrationPath.updateQuestion.success');

            return new RedirectResponse(
                $this->router->generate('admin_type_registration_path_show', ['event' => $event->getId()])
            );
        }

        return $this->engine->renderResponse(
            '@Admin/Type/RegistrationPath/updateQuestion.html.twig',
            [
                'event' => $event,
                'form' => $updateQuestionForm->createView(),
            ]
        );
    }
}
