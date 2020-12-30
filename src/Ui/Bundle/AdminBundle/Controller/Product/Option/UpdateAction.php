<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Product\Option;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Product\Option\UpdateOption;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Option\UpdateOptionType;
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

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var RouterInterface */
    private $router;

    /** @var EngineInterface */
    private $engine;

    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        FormFactoryInterface $formFactory,
        CommandBusInterface $commandBus,
        FlashBagInterface $flashBag,
        RouterInterface $router,
        EngineInterface $engine,
        HappeningRepositoryInterface $happeningRepository
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->formFactory = $formFactory;
        $this->commandBus = $commandBus;
        $this->flashBag = $flashBag;
        $this->router = $router;
        $this->engine = $engine;
        $this->happeningRepository = $happeningRepository;
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Product $product
     *
     * @return RedirectResponse|Response
     */
    public function __invoke(Request $request, Event $event, Product $product)
    {
        if (!$this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $event)
            || $product->getEvent() !== $event
            || false === $product->isOption()
        ) {
            throw new AccessDeniedException('Access denied to this page');
        }

        $happenings = $this->happeningRepository->findByEvent($event);

        $update = new UpdateOption($product);
        $form   = $this->formFactory->create(UpdateOptionType::class, $update, [
            'submit'  => true,
            'product' => $product,
            'locale'  => $event->getAvailableLocale($request->getLocale()),
            'happenings' => $happenings,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($update);
            $this->flashBag->add('success', 'flash.admin.product.update.success');

            return new RedirectResponse($this->router->generate('admin_product', ['event' => $event->getId()]));
        }

        return $this->engine->renderResponse('AdminBundle:Product:updateOption.html.twig', [
            'event'   => $event,
            'form'    => $form->createView(),
            'product' => $product,
        ]);
    }
}
