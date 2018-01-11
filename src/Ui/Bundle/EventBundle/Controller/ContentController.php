<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Query\Content\TermsOfSaleViewQuery;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentController extends Controller
{
    /**
     * @param Request     $request
     * @param Sheet       $sheet
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function termsOfSaleAction(Request $request, Sheet $sheet, EventDomain $eventDomain)
    {
        $termsOfSaleView = $this->get('tactician.commandbus')->handle(
            new TermsOfSaleViewQuery($eventDomain->getEvent(), $request->getLocale())
        );

        return $this->render('EventBundle:Package:terms-of-sale.html.twig', [
            'sheet'   => $sheet,
            'event'   => $eventDomain->getEvent(),
            'content' => $termsOfSaleView->content
        ]);
    }
}
