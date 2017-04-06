<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Tip\Create;
use Proximum\Vimeet\Application\Command\Tip\Update;
use Proximum\Vimeet\Application\Query\Tip\TipViewQuery;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\UpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TipController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $tipViewQuery = new TipViewQuery($request->query->get('page', 1));

        $tipListView = $this->get('tactician.commandbus')->handle($tipViewQuery);

        return $this->render('AdminBundle:Tip:list.html.twig',[
            'tips' => $tipListView,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');

        $command = new Create($this->getParameter('infrastructure.default_locales'));
        $form    = $this->createForm(CreateType::class, $command);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);
            $this->addFlash(
                'success',
                $this->get('translator')->trans('flash.admin.tip.create.success', [], 'flashes')
            );

            return $this->redirectToRoute('admin_tip_list');
        }

        return $this->render('AdminBundle:Tip:create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Tip     $tip
     *
     * @return Response
     */
    public function updateAction(Request $request, Tip $tip)
    {
        $this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN');
        
        $command = new Update($tip);
        $form    = $this->createForm(UpdateType::class, $command);
        
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);
            $this->addFlash(
                'success',
                $this->get('translator')->trans('flash.admin.tip.update.success', [], 'flashes')
            );

            return $this->redirectToRoute('admin_tip_list');
        }
        
        return $this->render('AdminBundle:Tip:update.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
