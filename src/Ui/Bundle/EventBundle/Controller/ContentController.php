<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Domain\Model\Event\Content;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return Response
     */
    public function termsOfSaleAction(Request $request, EventDomain $eventDomain)
    {
        $termsOfSale = $this
            ->get('repository.event.content_repository')
            ->findByEventAndType($eventDomain->getEvent(), Content::TYPE_TERMS_OF_SALE)
        ;

        $content = $termsOfSale->getValue($request->getLocale(), $eventDomain->getEvent()->getFallback());

        return $this->render('EventBundle:Package:terms-of-sale.html.twig', [
            'content' => $content
        ]);
    }
}
