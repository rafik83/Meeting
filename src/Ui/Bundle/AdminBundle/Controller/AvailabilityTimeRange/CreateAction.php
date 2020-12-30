<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\AvailabilityTimeRange;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\AvailabilityTimeRange\Create;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\AvailabilityTimeRange\CreateType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CreateAction
{
    private const TEMPLATE = 'AdminBundle:AvailabilityTimeRange:create.html.twig';

    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var EngineInterface */
    private $engine;

    /** @var RouterInterface */
    private $router;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        EngineInterface $engine,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->engine = $engine;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @throws AccessDeniedException
     *
     * @return Response|RedirectResponse
     */
    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_ORGANIZE')) {
            throw new AccessDeniedException('Only Admin and organizer can access this page');
        }

        if (!$event->hasDay()) {
            $this->flashBag->add('error', 'flash.admin.availabilityTimeRange.eventHasNoDay');

            return new RedirectResponse($this->router->generate('admin_event_availability_time_range_list', [
                'event' => $event->getId(),
            ]));
        }

        $create = new Create($event);
        $form = $this->formFactory->create(CreateType::class, $create, [
            'timezone' => $event->getTimeZone(),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($create);
            $this->flashBag->add('success', 'flash.admin.availabilityTimeRange.create.success');

            return new RedirectResponse($this->router->generate('admin_event_availability_time_range_list', [
                'event' => $event->getId(),
            ]));
        }

        return $this->engine->renderResponse(self::TEMPLATE, [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }
}
