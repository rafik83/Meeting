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
use Proximum\Vimeet\Application\Exception\Tip\TipException;
use Proximum\Vimeet\Application\Query\Tip\TipViewQuery;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\CreateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TipsController extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request)
    {
        $tipViewQuery = new TipViewQuery($request->query->get('page', 1));
        
        $tipListView = $this->get('tactician.commandbus')->handle($tipViewQuery);
        
        return $this->render('AdminBundle:Tips:list.html.twig',[
            'tips' => $tipListView,
        ]);
    }
    
    public function createAction(Request $request)
    {
        $command    = new Create();
        $form       = $this->createForm(CreateType::class, $command);
        
        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($command);
                $this->addFlash(
                    'success',
                    $this->get('translator')->trans('flash.admin.tip.create.success', [], 'flashes')
                );
                
                return $this->redirectToRoute('admin_tips_list');
            } catch (TipException $exception) {
                $this->addFlash(
                    'error',
                    $this->get('translator')->trans('flash.admin.tip.create.failure', [], 'flashes')
                );
            }
        }
        
        return $this->render('AdminBundle:Tips:create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
