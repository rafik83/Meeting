<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller;

use Proximum\Vimeet\Application\Command\Package\UpdateStep;
use Proximum\Vimeet\Application\Exception\Package\BoughtParticipantAlreadyAddedException;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Package\ChoosePaymentModeType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Package\UpdateStepType;
use Proximum\Vimeet\Domain\Model\EventView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PackageController extends BaseController
{
    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     * @param int       $step
     *
     * @return Response
     */
    public function updateStepAction(Request $request, EventView $eventView, Sheet $sheet, $step)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessForNonParticipant($sheet->getParticipants());

        $packageTemplate = $sheet->getType()->getPackageTemplate();

        if (!isset($packageTemplate[$step])) {
            throw new \InvalidArgumentException();
        }

        $updateStep = new UpdateStep($sheet, $step);
        $form       = $this->createForm(new UpdateStepType(), $updateStep, [
            'template' => $packageTemplate[$step]['template'],
            'locale'   => $request->getLocale(),
            'sheet'    => $sheet,
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this
                    ->get('vimeet_infrastructure.vimeet.application.command.package.update_step_handler')
                    ->handle($updateStep);

                if (isset($packageTemplate[$step + 1])) {
                    $this->addFlash('success', 'flash.package.update_step.success');

                    // Go to the next step
                    return $this->redirectToRoute('event_sheet_package_update_step', [
                        'subdomain' => $request->attributes->get('subdomain'),
                        'id'        => $sheet->getId(),
                        'step'      => $step + 1,
                    ]);
                } else {
                    $this->addFlash('success', 'flash.package.final_step.success');

                    // Go to the sheet
                    return $this->redirectToRoute('event_sheet', [
                        'subdomain' => $request->attributes->get('subdomain'),
                        'id'        => $sheet->getId(),
                    ]);
                }
            } catch (BoughtParticipantAlreadyAddedException $exception) {
                $this->addBoughtParticipantCanNotBeUncheckedErrorOnForm($form, $packageTemplate[$step], $updateStep->packageData, 'packageData');
                // Go to the payment mode
                return $this->redirectToRoute('event_sheet_package_payment_mode', [
                    'subdomain' => $request->attributes->get('subdomain'),
                    'id'        => $sheet->getId(),
                ]);
            }
        }

        return $this->render('VimeetAppBundle:Package:updateStep.html.twig', [
            'eventView'           => $eventView,
            'sheet'               => $sheet,
            'stepPackageTemplate' => $packageTemplate[$step],
            'form'                => $form->createView(),
        ]);
    }

    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return RedirectResponse|Response
     */
    public function paymentModeAction(Request $request, EventView $eventView, Sheet $sheet)
    {
        $form = $this->createForm(new ChoosePaymentModeType());
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', 'flash.package.payment_mode.success');

            // Go to the sheet
            return $this->redirectToRoute('event_sheet', [
                'subdomain' => $request->attributes->get('subdomain'),
                'id'        => $sheet->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Package:paymentMode.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView(),
        ]);
    }
}
