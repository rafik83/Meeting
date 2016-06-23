<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Product\CreateOption;
use Proximum\Vimeet\Application\Command\Product\CreatePlan;
use Proximum\Vimeet\Application\Command\Product\CreateParticipant;
use Proximum\Vimeet\Application\Command\Product\CreatePlanning;
use Proximum\Vimeet\Application\Command\Product\Option\UpdateOption;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\CreateOptionType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\CreatePlanType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\CreateParticipantType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\CreatePlanningType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\UpdateOptionType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

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

        $products = $this->get('vimeet_infrastructure.repository.product_repository')->findByEvent($event);

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
    public function createOptionAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $create = new CreateOption($event);
        $form   = $this->createForm(CreateOptionType::class, $create, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($create);
            $this->addFlash('success', 'flash.admin.product.create.success');

            return $this->redirectToRoute('admin_product', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Product:createOption.html.twig', [
            'event' => $event,
            'form'  => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param Event $event
     * @param Product $product
     * @return RedirectResponse|Response
     */
    public function updateOptionAction(Request $request, Event $event, Product $product)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $update = new UpdateOption($product);
        $form = $this->createForm(UpdateOptionType::class, $update, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($update);
            $this->addFlash('success', 'flash.admin.product.update.success');

            return $this->redirectToRoute('admin_product', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Product:updateOption.html.twig', [
            'event' => $event,
            'form'  => $form->createView()
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
            'locale' => $request->getLocale(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($create);
            $this->addFlash('success', 'flash.admin.product.create.success');

            return $this->redirectToRoute('admin_product', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Product:createPlan.html.twig', [
            'event' => $event,
            'form'  => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param Event $event
     *
     * @param Product $product
     * @return RedirectResponse|Response
     */
    public function updatePlanAction(Request $request, Event $event, Product $product)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $create = new CreatePlan($event);
        $form   = $this->createForm(CreatePlanType::class, $create, [
            'submit' => true,
            'event'  => $event,
            'locale' => $request->getLocale(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($create);
            $this->addFlash('success', 'flash.admin.product.create.success');

            return $this->redirectToRoute('admin_product', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Product:updatePlan.html.twig', [
            'event' => $event,
            'form'  => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function createParticipantAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $create = new CreateParticipant($event);
        $form   = $this->createForm(CreateParticipantType::class, $create, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($create);
            $this->addFlash('success', 'flash.admin.product.create.success');

            return $this->redirectToRoute('admin_product', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Product:createParticipant.html.twig', [
            'event' => $event,
            'form'  => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param Event $event
     *
     * @param Product $product
     * @return RedirectResponse|Response
     */
    public function updateParticipantAction(Request $request, Event $event, Product $product)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $create = new CreateParticipant($event);
        $form   = $this->createForm(CreateParticipantType::class, $create, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($create);
            $this->addFlash('success', 'flash.admin.product.create.success');

            return $this->redirectToRoute('admin_product', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Product:updateParticipant.html.twig', [
            'event' => $event,
            'form'  => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function createPlanningAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $create = new CreatePlanning($event);
        $form   = $this->createForm(CreatePlanningType::class, $create, ['submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($create);
            $this->addFlash('success', 'flash.admin.product.create.success');

            return $this->redirectToRoute('admin_product', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Product:createPlanning.html.twig', [
            'event' => $event,
            'form'  => $form->createView()
        ]);
    }
}
