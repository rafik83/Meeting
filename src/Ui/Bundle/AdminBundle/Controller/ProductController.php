<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Command\Product\Import\Import;
use Proximum\Vimeet\Application\Command\Product\Plan\CreatePlan;
use Proximum\Vimeet\Application\Command\Product\Plan\UpdatePlan;
use Proximum\Vimeet\Application\Command\Product\Planning\CreatePlanning;
use Proximum\Vimeet\Application\Command\Product\Planning\UpdatePlanning;
use Proximum\Vimeet\Application\Query\Product\ProductsViewQuery;
use Proximum\Vimeet\Application\Query\Product\ProductsViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Import\ImportType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Plan\CreatePlanType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Plan\UpdatePlanType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Planning\CreatePlanningType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Planning\UpdatePlanningType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends AbstractController
{
    private ProductsViewQueryHandler $productsViewQueryHandler;
    private CommandBusInterface $commandBus;

    public function __construct(
        ProductsViewQueryHandler $productsViewQueryHandler,
        CommandBusInterface $commandBus
    ) {
        $this->productsViewQueryHandler = $productsViewQueryHandler;
        $this->commandBus = $commandBus;
    }

    public function listAction(Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $products = $this->productsViewQueryHandler->handle(new ProductsViewQuery($event));

        return $this->render('AdminBundle:Product:list.html.twig', [
            'event' => $event,
            'products' => $products,
        ]);
    }

    public function createPlanAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $create = new CreatePlan($event);
        $form = $this->createForm(CreatePlanType::class, $create, [
            'submit' => true,
            'event' => $event,
            'locale' => $event->getAvailableLocale($request->getLocale()),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($create);
            $this->addFlash('success', 'flash.admin.product.create.success');

            return $this->redirectToRoute('admin_product', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Product:createPlan.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    public function updatePlanAction(Request $request, Event $event, Product $product): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $update = new UpdatePlan($product);
        $form = $this->createForm(UpdatePlanType::class, $update, [
            'submit' => true,
            'product' => $product,
            'locale' => $event->getAvailableLocale($request->getLocale()),
            'event' => $event,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($update);
            $this->addFlash('success', 'flash.admin.product.update.success');

            return $this->redirectToRoute('admin_product', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Product:updatePlan.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'plan' => $product,
        ]);
    }

    public function createPlanningAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $create = new CreatePlanning($event);
        $form = $this->createForm(CreatePlanningType::class, $create, [
            'submit' => true,
            'event' => $event,
            'locale' => $event->getAvailableLocale($request->getLocale()),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($create);
            $this->addFlash('success', 'flash.admin.product.create.success');

            return $this->redirectToRoute('admin_product', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Product:createPlanning.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    public function updatePlanningAction(Request $request, Event $event, Product $product): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $update = new UpdatePlanning($product);
        $form = $this->createForm(UpdatePlanningType::class, $update, [
            'submit' => true,
            'product' => $product,
            'locale' => $event->getAvailableLocale($request->getLocale()),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($update);
            $this->addFlash('success', 'flash.admin.product.update.success');

            return $this->redirectToRoute('admin_product', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Product:updatePlanning.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'product' => $product
        ]);
    }

    /**
     * Import all products and package templates from an Event x to the current Event
     */
    public function importAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $duplicate = new Import($event);
        $form = $this->createForm(ImportType::class, $duplicate, [
            'action' => $this->generateUrl('admin_product_template_import', ['event' => $event->getId()]),
            'admin' => $this->getUser(),
            'event' => $event,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($duplicate);
            $this->addFlash('success', 'flash.admin.product.import.success');

            return $this->redirectToRoute('admin_product', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Product:import.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }
}
