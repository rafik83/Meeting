<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Rooming\Accommodation;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Rooming\Accommodation\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Rooming\Accommodation;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rooming\Accommodation\UpdateType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class UpdateAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var Environment */
    private $twig;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        Environment $twig,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        RouterInterface $router,
        FlashBagInterface $flashBag
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->router = $router;
        $this->flashBag = $flashBag;
    }

    public function __invoke(Request $request, Event $event, Accommodation $accommodation): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            || !$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $accommodation->getEvent() !== $event
        ) {
            throw new AccessDeniedException('Access denied');
        }

        if (!$event->hasDay()) {
            $this->flashBag->add('error', 'flash.event.daysMustBeDefined');

            return new RedirectResponse($this->router->generate('admin_event_read', [
                'event' => $event->getId(),
            ]));
        }

        $update = new Update($accommodation);
        $form = $this->formFactory->create(UpdateType::class, $update, [
            'submit' => true,
            'firstDay' => $event->getFirstDay()->getBegin(),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($update);
            $this->flashBag->add('success', 'flash.event.accommodation.updated');

            return new RedirectResponse($this->router->generate('admin_event_rooming_accommodation_update', [
                'event' => $event->getId(),
                'accommodation' => $accommodation->getId(),
            ]));
        }

        return new Response($this->twig->render('@Admin/Rooming/Accommodation/update.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]));
    }
}
