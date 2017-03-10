<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    /**
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param Invoice     $invoice
     * @param string      $hash
     *
     * @return Response
     */
    public function showAction(EventDomain $eventDomain, Sheet $sheet, Invoice $invoice, $hash)
    {
        if ($invoice->getSheet() !== $sheet || $invoice->getHash() !== $hash) {
            throw $this->createNotFoundException();
        }

        $event = $eventDomain->getEvent();

        return $this->render('EventBundle:Invoice:show.html.twig', [
            'event' => $event,
            'invoice' => $invoice,
        ]);
    }
}
