<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Billing\UpdateInfo;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Billing\UpdateInfoType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BillingController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return Response
     */
    public function infoAction(Request $request, EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if (!$sheet->hasUser($this->getUser()) || $sheet->getEvent() !== $eventDomain->getEvent()) {
            throw $this->createNotFoundException(
                sprintf('The current user %s is not part of this sheet %s', $this->getUser()->getId(), $sheet->getId())
            );
        }

        $info    = $this->get('repository.billing_info_repository')->getBySheet($sheet) ? : new BillingInfo($sheet);
        $country = $sheet->getEvent()->getCountry();

        if (null === $info->getId()) {
            $this->get('billing.prefiller')->prefill($info);
        }

        $command = new UpdateInfo($info);
        $form    = $this->createForm(UpdateInfoType::class, $command, ['submit' => true, 'country' => $country]);

        $packageCompleteBilling = $this->getFlash('package_complete_billing_info');
        $funnel = null;

        if (null !== $this->getFlash('package_funnel_billing_info')) {
            $funnel = $this->get('package.funnel.funnel_factory')->create($sheet, $request->getLocale());
            $this->addFlash('package_funnel_billing_info', true);
        }

        if (null !== $packageCompleteBilling) {
            $this->addFlash('package_complete_billing_info', $packageCompleteBilling);
        }

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);

            // Redirect to package summary if coming from Package Summary
            if (null !== $packageCompleteBilling) {
                return $this->redirectToRoute('event_package_summary', [
                    'sheet' => $sheet->getId(),
                ]);
            } else {
                return $this->redirectToRoute('event_billing_info', [
                    'sheet' => $sheet->getId(),
                ]);
            }
        }

        return $this->render('EventBundle:Billing:info.html.twig', [
            'event'  => $eventDomain->getEvent(),
            'form'   => $form->createView(),
            'view'   => ['funnel' => $funnel]
        ]);
    }

    /**
     * Route used in navigation menu to redirect into billing info form
     * and clean flash to prevent the funnel display
     */
    public function infoClearFlashAction(Sheet $sheet)
    {
        $this->container->get('session')->getFlashBag()->set('package_funnel_billing_info', null);
        return $this->redirectToRoute('event_billing_info', [
            'sheet' => $sheet->getId(),
            ]
        );
    }

    /**
     * @return null|mixed
     */
    private function getFlash($flash)
    {
        $sheet = $this->container->get('session')->getFlashBag()->get($flash);

        return array_shift($sheet);
    }
}
