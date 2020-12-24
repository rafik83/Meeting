<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Product\Participant;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Product\Participant\CreateParticipant;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\AvailabilityTimeRangeRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Participant\CreateParticipantType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CreateAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var EngineInterface */
    private $engine;

    /** @var AvailabilityTimeRangeRepositoryInterface */
    private $availabilityTimeRangeRepository;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        AvailabilityTimeRangeRepositoryInterface $availabilityTimeRangeRepository,
        CommandBusInterface $commandBus,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        EngineInterface $engine
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->engine = $engine;
        $this->availabilityTimeRangeRepository = $availabilityTimeRangeRepository;
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response|RedirectResponse
     */
    public function __invoke(Request $request, Event $event): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)) {
            throw new AccessDeniedException('This page is not accessible');
        }

        $availabilityTimeRanges = $this->availabilityTimeRangeRepository->findByEvent($event);

        $create = new CreateParticipant($event);
        $form = $this->formFactory->create(CreateParticipantType::class, $create, [
            'event'  => $event,
            'locale' => $event->getAvailableLocale($request->getLocale()),
            'submit' => true,
            'availabilityTimeRanges' => $availabilityTimeRanges,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($create);
            $this->flashBag->add('success', 'flash.admin.product.create.success');

            return new RedirectResponse($this->router->generate('admin_product', ['event' => $event->getId()]));
        }

        return $this->engine->renderResponse('AdminBundle:Product:createParticipant.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
