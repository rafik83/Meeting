<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Product\Import\Import;
use Proximum\Vimeet\Application\Command\Product\Plan\CreatePlan;
use Proximum\Vimeet\Application\Command\Product\Plan\UpdatePlan;
use Proximum\Vimeet\Application\Command\Product\Planning\CreatePlanning;
use Proximum\Vimeet\Application\Command\Product\Planning\UpdatePlanning;
use Proximum\Vimeet\Application\Query\Product\ProductsViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Import\ImportType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Plan\CreatePlanType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Plan\UpdatePlanType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Planning\CreatePlanningType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Planning\UpdatePlanningType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    /**
     * @param Event $event
     *
     * @return Response
     */
    public function listAction(Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $products = $this->get('query.product.products_view_query_handler')->handle(new ProductsViewQuery($event));

        return $this->render('AdminBundle:Product:list.html.twig', [
            'event'    => $event,
            'products' => $products,
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function createPlanAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $create = new CreatePlan($event);
        $form   = $this->createForm(CreatePlanType::class, $create, [
            'submit' => true,
            'event'  => $event,
            'locale' => $event->getAvailableLocale($request->getLocale()),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($create);
            $this->addFlash('success', 'flash.admin.product.create.success');

            return $this->redirectToRoute('admin_product', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Product:createPlan.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Product $product
     *
     * @return RedirectResponse|Response
     */
    public function updatePlanAction(Request $request, Event $event, Product $product): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $update = new UpdatePlan($product);
        $form = $this->createForm(UpdatePlanType::class, $update, [
            'submit'  => true,
            'product' => $product,
            'locale'  => $event->getAvailableLocale($request->getLocale()),
            'event'   => $event,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($update);
            $this->addFlash('success', 'flash.admin.product.update.success');

            return $this->redirectToRoute('admin_product', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Product:updatePlan.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
            'plan'  => $product,
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function createPlanningAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $create = new CreatePlanning($event);
        $form = $this->createForm(CreatePlanningType::class, $create, [
            'submit' => true,
            'event'  => $event,
            'locale' => $event->getAvailableLocale($request->getLocale()),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($create);
            $this->addFlash('success', 'flash.admin.product.create.success');

            return $this->redirectToRoute('admin_product', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Product:createPlanning.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Product $product
     *
     * @return RedirectResponse|Response
     */
    public function updatePlanningAction(Request $request, Event $event, Product $product): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $update = new UpdatePlanning($product);
        $form = $this->createForm(UpdatePlanningType::class, $update, [
            'submit'  => true,
            'product' => $product,
            'locale'  => $event->getAvailableLocale($request->getLocale()),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($update);
            $this->addFlash('success', 'flash.admin.product.update.success');

            return $this->redirectToRoute('admin_product', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Product:updatePlanning.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
            'product' => $product
        ]);
    }

    /**
     * Import all products and package templates from an Event x to the current Event
     *
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function importAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $duplicate = new Import($event);
        $form      = $this->createForm(ImportType::class, $duplicate, [
            'action' => $this->generateUrl('admin_product_template_import', ['event' => $event->getId()]),
            'admin'  => $this->getUser(),
            'event'  => $event,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($duplicate);
            $this->addFlash('success', 'flash.admin.product.import.success');

            return $this->redirectToRoute('admin_product', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Product:import.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
