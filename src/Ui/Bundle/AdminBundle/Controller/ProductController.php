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
use Proximum\Vimeet\Application\Command\Product\CreatePackage;
use Proximum\Vimeet\Application\Command\Product\CreateParticipant;
use Proximum\Vimeet\Application\Command\Product\CreatePlanning;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\CreateOptionType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\CreatePackageType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\CreateParticipantType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\CreatePlanningType;
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
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function createPackageAction(Request $request, Event $event)
    {
        $create = new CreatePackage($event);
        $form   = $this->createForm(CreatePackageType::class, $create, ['submit' => true, 'event' => $event]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($create);
            $this->addFlash('success', 'flash.admin.product.create.success');

            return $this->redirectToRoute('admin_product', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Product:createPackage.html.twig', [
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
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function createPlanningAction(Request $request, Event $event)
    {
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
