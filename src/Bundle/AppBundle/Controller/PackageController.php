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
     * @return Response|RedirectResponse
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
            'template' => $sheet->getType()->getPackageTemplate()[$step]['template'],
            'locale'   => $request->getLocale(),
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this
                ->get('vimeet_infrastructure.vimeet.application.command.package.update_step_handler')
                ->handle($updateStep);

            return $this->redirect($this->urlAfterUpdateStep($request, $sheet, $step));
        }

        return $this->render('VimeetAppBundle:Package:updateStep.html.twig', [
            'eventView'           => $eventView,
            'sheet'               => $sheet,
            'stepPackageTemplate' => $sheet->getType()->getPackageTemplate()[$step],
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
        $packageTemplate = $sheet->getType()->getPackageTemplate();

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
        $this->addFlash('success', 'flash.package.update_step.success');

        $redirectTo      = $request->get('redirect_to');
        $packageTemplate = $sheet->getType()->getPackageTemplate();

        if (null !== $redirectTo) {
            return $redirectTo;
        } elseif (isset($packageTemplate[$step + 1])) {
            return $this->generateUrl('event_sheet_package_update_step', [
                'subdomain' => $request->attributes->get('subdomain'),
                'id'        => $sheet->getId(),
                'step'      => $step + 1,
            ]);
        }

        return $this->generateUrl('event_sheet', [
            'subdomain' => $request->attributes->get('subdomain'),
            'id'        => $sheet->getId(),
        ]);
    }
}
