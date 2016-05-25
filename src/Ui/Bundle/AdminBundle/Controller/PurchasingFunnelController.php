<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\PurchasingFunnel\Create;
use Proximum\Vimeet\Application\Command\PurchasingFunnel\Update;
use Proximum\Vimeet\Domain\Model\PurchasingFunnel;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PurchasingFunnel\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PurchasingFunnel\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PurchasingFunnelController extends Controller
{
    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $events            = $this->get('vimeet_infrastructure.repository.event_repository')->getEventsByAdmin($this->getUser());
        $purchasingFunnels = $this->get('repository.purchasing_funnel_repository')->findByEvents($events);

        $create = new Create();
        $form   = $this->createForm(CreateType::class, $create, ['submit' => true, 'user' => $this->getUser()]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $result = $this->get('tactician.commandbus')->handle($create);

            return $this->redirectToRoute('admin_purchasing_funnel_update', [
                'purchasingFunnel' => $result->purchasingFunnel->getId(),
            ]);
        }

        return $this->render('AdminBundle:PurchasingFunnel:list.html.twig', [
            'purchasing_funnels' => $purchasingFunnels,
            'form'               => $form->createView(),
        ]);
    }

    /**
     * @param Request          $request
     * @param PurchasingFunnel $purchasingFunnel
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, PurchasingFunnel $purchasingFunnel)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        if (!$this->isGranted('ROLE_SUPER_ADMIN') && !$this->getUser()->hasEvent($purchasingFunnel->getEvent())) {
            throw $this->createAccessDeniedException('You are not allowed to edit this purchasing funnel.');
        }

        $update = new Update($purchasingFunnel);
        $form   = $this->createForm(UpdateType::class, $update, [
            'event'  => $purchasingFunnel->getEvent(),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($update);
            $this->addFlash('success', 'flash.admin.template.products_selection.update.success');

            return $this->redirectToRoute('admin_purchasing_funnel_list');
        }

        return $this->render('AdminBundle:PurchasingFunnel:update.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
