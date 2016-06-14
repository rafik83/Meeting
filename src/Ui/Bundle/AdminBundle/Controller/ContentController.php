<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Event\Content\UpdateTermsOfSale;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Content\UpdateTermsOfSaleType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function updateAction(Request $request, Event $event)
    {
        $content = $this
            ->get('repository.event.content_repository')
            ->getTermsOfSalesByEvent($event);

        if (null === $content) {
            throw $this->createNotFoundException('Terms Of Sales not found');
        }

        $update = new UpdateTermsOfSale($content);
        $form   = $this->createForm(UpdateTermsOfSaleType::class, $update, [
            'content' => $content,
            'action'  => $this->generateUrl('admin_content_update_terms_of_sales', [
                'event' => $event->getId(),
            ])
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($update);
            $this->addFlash('success', 'flash.content.update_terms_of_sale.success');

            return $this->redirectToRoute('admin_content_update_terms_of_sales', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Content:update.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
