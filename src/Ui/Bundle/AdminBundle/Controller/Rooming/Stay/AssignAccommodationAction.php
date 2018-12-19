<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Rooming\Stay;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Rooming\Stay\AssignAccommodation;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rooming\Stay\AssignAccommodationType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Templating\EngineInterface;

class AssignAccommodationAction
{
    /** @var EngineInterface */
    private $engine;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        EngineInterface $engine,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        RouterInterface $router
    ) {
        $this->engine = $engine;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->router = $router;
    }

    public function __invoke(Request $request, Event $event, User $user, string $arrivalDate, string $departureDate): Response
    {
        $assignAccommodation = new AssignAccommodation($event, $user, new \DateTime($arrivalDate), new \DateTime($departureDate));
        $form = $this->formFactory->create(AssignAccommodationType::class, $assignAccommodation, [
            'submit' => true,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($assignAccommodation);

            return new RedirectResponse(
                $this->router->generate('admin_event_rooming_list', [
                    'event' => $event->getId(),
                ])
            );
        }

        return new Response($this->engine->render('@Admin/Rooming/Stay/assignAccommodation.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]));
    }
}
