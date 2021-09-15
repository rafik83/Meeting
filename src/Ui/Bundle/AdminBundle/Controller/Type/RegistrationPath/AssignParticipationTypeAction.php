<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Type\RegistrationPath;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Type\RegistrationPath\AssignParticipationType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Answer;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\RegistrationPath\AssignParticipationTypeType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class AssignParticipationTypeAction
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

    /** @var RouterInterface */
    private $router;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag,
        FormFactoryInterface $formFactory,
        Environment $twig,
        RouterInterface $router
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->router = $router;
    }

    public function __invoke(Request $request, AdminDomain $adminDomain, Event $event, Answer $answer): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $answer->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied');
        }

        if ($answer->hasAlreadyNextStep()) {
            throw new \LogicException('Answer already has a next step');
        }

        $admin = $adminDomain->getAdmin();
        $locale = $event->getAvailableLocale($request->getLocale());

        $assignParticipationType = new AssignParticipationType($answer);
        $assignParticipationTypeForm = $this->formFactory->create(
            AssignParticipationTypeType::class,
            $assignParticipationType,
            ['event' => $event, 'locale' => $locale, 'admin' => $admin, 'submit' => true]
        );

        $assignParticipationTypeForm->handleRequest($request);

        if ($assignParticipationTypeForm->isSubmitted() && $assignParticipationTypeForm->isValid()) {
            $this->commandBus->handle($assignParticipationType);

            $this->flashBag->add('success', 'flash.registrationPath.assignParticipationType.success');

            return new RedirectResponse(
                $this->router->generate('admin_type_registration_path_show', ['event' => $event->getId()])
            );
        }

        return new Response($this->twig->render(
            '@Admin/Type/RegistrationPath/assignParticipationType.html.twig',
            [
                'event' => $event,
                'questionTitle' => $answer->getQuestion()->getTitle($locale),
                'answerTitle' => $answer->getTitle($locale),
                'form' => $assignParticipationTypeForm->createView(),
            ]
        ));
    }
}
