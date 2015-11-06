<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use Proximum\Vimeet\Application\Command\Package\UpdateStep;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Package\ChoosePaymentModeType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Package\UpdateStepType;
use Proximum\Vimeet\Domain\Model\EventView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PackageController extends BaseController
{
    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     * @param int       $step
     *
     * @return RedirectResponse|Response
     *
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function updateStepAction(Request $request, EventView $eventView, Sheet $sheet, $step)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessForNonParticipant($sheet->getParticipants());
        $this->denyAccessPackageStepNotExists($sheet, $step);

        $updateStep = new UpdateStep($sheet, $step);
        $form       = $this->createForm(new UpdateStepType(), $updateStep, [
            'template' => $sheet->getTypePackageTemplate()[$step]['template'],
            'locale'   => $request->getLocale(),
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this
                ->get('vimeet_infrastructure.vimeet.application.command.package.update_step_handler')
                ->handle($updateStep);

            return $this->redirect($this->urlAfterUpdateStep($request, $sheet, $step));
        }

        return $this->render('VimeetAppBundle:Event/Package:updateStep.html.twig', [
            'eventView'           => $eventView,
            'sheet'               => $sheet,
            'stepPackageTemplate' => $sheet->getTypePackageTemplate()[$step],
            'form'                => $form->createView(),
        ]);
    }

    /**
     * @param Sheet $sheet
     * @param int   $step
     *
     * @throws NotFoundHttpException
     */
    private function denyAccessPackageStepNotExists(Sheet $sheet, $step)
    {
        $packageTemplate = $sheet->getTypePackageTemplate();

        if (!isset($packageTemplate[$step])) {
            throw $this->createNotFoundException();
        }
    }

    /**
     * @param Request $request
     * @param Sheet   $sheet
     * @param int     $step
     *
     * @return string
     */
    private function urlAfterUpdateStep(Request $request, Sheet $sheet, $step)
    {
        $redirectTo      = $request->get('redirect_to');
        $packageTemplate = $sheet->getTypePackageTemplate();

        if (null !== $redirectTo) {
            $this->addFlash('success', 'flash.package.update_step.success');

            return $redirectTo;
        } elseif ($nextStep = self::nextStep($step, array_keys($packageTemplate))) {
            $this->addFlash('success', 'flash.package.update_step.success');

            return $this->generateUrl('event_sheet_package_update_step', [
                'subdomain' => $request->attributes->get('subdomain'),
                'id'        => $sheet->getId(),
                'step'      => $nextStep,
            ]);
        }

        $this->addFlash('success', 'flash.package.final_step.success');

        return $this->generateUrl('event_sheet_package_payment_mode', [
            'subdomain' => $request->attributes->get('subdomain'),
            'id'        => $sheet->getId(),
        ]);
    }

    /**
     * @param string $current
     * @param array  $array
     *
     * @return string|null
     */
    public static function nextStep($current, array $array)
    {
        foreach ($array as $key => $value) {
            if ($value === $current) {
                return isset($array[$key + 1]) ? $array[$key + 1] : null;
            }
        }

        return null;
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

        return $this->render('VimeetAppBundle:Event/Package:paymentMode.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView(),
        ]);
    }
}
