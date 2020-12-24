<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Product\Participant;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Product\Participant\UpdateParticipant;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\AvailabilityTimeRangeRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Participant\UpdateParticipantType;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UpdateAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var RouterInterface */
    private $router;

    /** @var EngineInterface */
    private $engine;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var AvailabilityTimeRangeRepositoryInterface */
    private $availabilityTimeRangeRepository;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        AvailabilityTimeRangeRepositoryInterface $availabilityTimeRangeRepository,
        EngineInterface $engine,
        RouterInterface $router,
        CommandBusInterface $commandBus,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->availabilityTimeRangeRepository = $availabilityTimeRangeRepository;
        $this->router = $router;
        $this->engine = $engine;
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->flashBag = $flashBag;
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Product $product
     *
     * @return RedirectResponse|Response
     */
    public function __invoke(Request $request, Event $event, Product $product): Response
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $product->getEvent() !== $event
            || !$product->isParticipant()
        ) {
            throw new AccessDeniedException('This page is not accessible');
        }

        $availabilityTimeRanges = $this->availabilityTimeRangeRepository->findByEvent($event);
        $update = new UpdateParticipant($product);
        $form = $this->formFactory->create(UpdateParticipantType::class, $update, [
            'availabilityTimeRanges' => $availabilityTimeRanges,
            'event' => $event,
            'locale' => $event->getAvailableLocale($request->getLocale()),
            'product' => $product,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($update);
            $this->flashBag->add('success', 'flash.admin.product.update.success');

            return new RedirectResponse($this->router->generate('admin_product', ['event' => $event->getId()]));
        }

        return $this->engine->renderResponse('AdminBundle:Product:updateParticipant.html.twig', [
            'event'   => $event,
            'form'    => $form->createView(),
            'product' => $product,
        ]);
    }
}
