<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Billing\Update;
use Proximum\Vimeet\Application\Exception\Data\RequiredDataEmptyException;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Billing\BillingUpdateType;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\View\EventView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BillingController extends Controller
{
    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return Response
     */
    public function billingAction(Request $request, EventView $eventView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$sheet->hasUser($this->getUser())) {
            throw $this->createAccessDeniedException('You can not update this data');
        }

        $update = new Update($sheet, $sheet->getBillingData());

        $form = $this->createForm(BillingUpdateType::class, $update, [
            'template' => $sheet->getEvent()->getBillingTemplate(),
            'locale'   => $request->getLocale(),
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($update);
                $this->addFlash('success', 'flash.sheet.update_billing.success');

                return $this->redirectToRoute('event_sheet_package_payment_mode', [
                    'sheet' => $sheet->getId(),
                ]);
            } catch (RequiredDataEmptyException $exception) {
                foreach ($exception->getKeys() as $key) {
                    $form->get($key)->addError(new FormError('validators.field.required'));
                }
            }
        }

        return $this->render('EventBundle:Billing:billing.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'form'      => $form->createView(),
        ]);
    }
}
