<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Query\Tip\TipViewQuery;
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
        
        return $this->render('AdminBundle:Tips:create.html.twig', []);
    }
}
