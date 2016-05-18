<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Product\Create;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\CreateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ProductController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event)
    {
        $products = $this->get('vimeet_infrastructure.repository.product_repository')->findByEvent($event);

        return $this->render(
            'AdminBundle:Product:list.html.twig',
            [
                'event'    => $event,
                'products' => $products,
            ]
        );
    }

    /**
     * @param Request $request
     * @param Event $event
     *
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request, Event $event)
    {
        $create = new Create($event);
        $form   = $this->createForm(CreateType::class, $create, [
            'method' => 'POST',
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($create);
            $this->addFlash('success', 'flash.admin.product.create.success');

            return $this->redirectToRoute('admin_product', [
                'event' => $event->getId(),
            ]);
        }

        return $this->render(
            'AdminBundle:Product:create.html.twig', [
                'event' => $event,
                'form'  => $form->createView()
            ]
        );
    }
}
