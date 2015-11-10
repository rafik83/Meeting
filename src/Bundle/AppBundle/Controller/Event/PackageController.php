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
use Proximum\Vimeet\Application\Exception\Package\BoughtParticipantAlreadyAddedException;
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
            'sheet'    => $sheet,
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this
                    ->get('vimeet_infrastructure.vimeet.application.command.package.update_step_handler')
                    ->handle($updateStep);

                return $this->redirect($this->urlAfterUpdateStep($request, $sheet, $step));
            } catch (BoughtParticipantAlreadyAddedException $exception) {
                $packageTemplate = $sheet->getTypePackageTemplate();
                $this->addBoughtParticipantCanNotBeUncheckedErrorOnForm(
                    $form,
                    $packageTemplate[$step],
                    $updateStep->packageData,
                    $form->get('packageData'));
            }
        }

        return $this->render('VimeetAppBundle:Event/Package:updateStep.html.twig', [
            'eventView'           => $eventView,
            'sheet'               => $sheet,
            'stepPackageTemplate' => $sheet->getTypePackageTemplate()[$step],
            'form'                => $form->createView(),
        ]);
    }


    /**
     * @param Request $request
     * @param EventView $eventView
     * @param Sheet $sheet
     *
     * @return Response
     */
    public function cartAction(Request $request, EventView $eventView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $this->denyAccessForNonParticipant($sheet->getParticipants());

        $cart = $this->renderCart($sheet->getTypePackageTemplate(), $sheet->getPackageData(), $request->getLocale());

        return $this->render('VimeetAppBundle:Event/Package:cart.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'cart'      => $cart,
        ]);
    }

    /**
     * @param array  $packageTemplate
     * @param array  $packageData
     * @param string $locale
     *
     * @return array $cart
     */
    private function renderCart(array $packageTemplate, array $packageData, $locale)
    {
        $cart  = [];
        $total = 0;

        foreach ($packageTemplate as $blockKey => $block) {
            $data            = [];
            $data['title']    = $block['title'][$locale];
            $data['options'] = [];
            $subTotal        = 0;

            foreach ($block['template'] as $templateKey => $template) {
                if (isset($template['type'])) {
                    $options = [];

                    if ($template['type'] === 'choice_with_description') {
                        if (isset($packageData[$blockKey][$templateKey]['value'])) {
                            $options['label']     = $template['label'][$locale];
                            $options['choice']    = $template['choices'][$packageData[$blockKey][$templateKey]['value']]['label'][$locale];
                            $options['quantity']  = 1;
                            $options['unitPrice'] = $template['choices'][$packageData[$blockKey][$templateKey]['value']]['unitPrice'];
                            $options['total']     = $options['quantity'] * $options['unitPrice'];
                        }
                    }

                    if ($template['type'] === 'lib_participant') {
                        if (isset($packageData[$blockKey][$templateKey]['participant'])
                            && $packageData[$blockKey][$templateKey]['participant'] === true
                        ) {
                            $options['label']     = $template['label'][$locale];
                            $options['quantity']  = isset($packageData[$blockKey][$templateKey]['participant_bought']) && $packageData[$blockKey][$templateKey]['participant_bought'] !== null ? $packageData[$blockKey][$templateKey]['participant_bought'] : 0;
                            $options['unitPrice'] = $template['unitPrice'];
                            $options['total']     = $options['quantity'] * $options['unitPrice'];
                        }
                    }

                    if ($template['type'] === 'lib_planning') {
                        if (isset($packageData[$blockKey][$templateKey]['planning'])
                            && $packageData[$blockKey][$templateKey]['planning'] === true
                        ) {
                            $options['label']     = $template['label'][$locale];
                            $options['quantity']  = isset($packageData[$blockKey][$templateKey]['planning_bought']) && $packageData[$blockKey][$templateKey]['planning_bought'] !== null ? $packageData[$blockKey][$templateKey]['planning_bought'] : 0;
                            $options['unitPrice'] = $template['unitPrice'];
                            $options['total']     = $options['quantity'] * $options['unitPrice'];
                        }
                    }

                    if ($template['type'] === 'upload_with_choices') {
                        if (isset($packageData[$blockKey][$templateKey]['value']['value'])) {
                            if (!isset($template['choices'][$packageData[$blockKey][$templateKey]['value']['value']]['placeholder'])
                            ) {
                                $options['label']     = $template['label'][$locale];
                                $options['choice']    = $template['choices'][$packageData[$blockKey][$templateKey]['value']['value']]['label'][$locale];
                                $options['quantity']  = isset($packageData[$blockKey][$templateKey]['value']['quantity']) ? $packageData[$blockKey][$templateKey]['value']['quantity'] : 1;
                                $options['unitPrice'] = $template['choices'][$packageData[$blockKey][$templateKey]['value']['value']]['unitPrice'];
                                $options['total']     = $options['quantity'] * $options['unitPrice'];
                            }
                        }

                    }

                    if ($template['type'] === 'lib_option') {
                        if (isset($packageData[$blockKey][$templateKey]['value']) && $packageData[$blockKey][$templateKey]['value'] !== false) {
                            $options['label']     = $template['label'][$locale];
                            $options['unitPrice'] = $template['unitPrice'];
                            $options['quantity']  = isset($packageData[$blockKey][$templateKey]['quantity']) && $packageData[$blockKey][$templateKey]['quantity'] !== null ? $packageData[$blockKey][$templateKey]['quantity'] : 1;
                            $options['total']     = $options['quantity'] * $options['unitPrice'];
                        }

                    }
                }

                if(isset($options['total'])) {
                    $total += $options['total'];
                    $subTotal += $options['total'];
                }

                if ($options !== []) {
                    array_push($data['options'], $options);
                }
            }

            $data['subTotal'] = $subTotal;
            array_push($cart, $data);
        }

        $cart['total'] = $total;

        return $cart;

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

        return $this->generateUrl('event_sheet_package_cart', [
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
    private static function nextStep($current, array $array)
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
