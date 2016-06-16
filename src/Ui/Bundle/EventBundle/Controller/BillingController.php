<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Billing\UpdateInfo;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\EventView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Billing\UpdateInfoType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BillingController extends Controller
{
    /**
     * @param Request   $request
     * @param EventView $eventView
     *
     * @return Response
     */
    public function infoAction(Request $request, EventView $eventView)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $sheet   = $this->get('sheet.sheet_guesser')->getUserSheet($this->getUser(), $eventView, $request->getLocale());
        $info    = $this->get('repository.billing_info_repository')->getBySheet($sheet) ? : new BillingInfo($sheet);
        $country = $sheet->getEvent()->getCountry();

        if (null === $info->getId()) {
            $this->get('billing.prefiller')->prefill($info);
        }

        $command = new UpdateInfo($info);
        $form    = $this->createForm(UpdateInfoType::class, $command, ['submit' => true, 'country' => $country]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);
            $this->addFlash('success', 'flash.billing.update_info.success');
        }

        return $this->render('EventBundle:Billing:info.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView(),
        ]);
    }
}
