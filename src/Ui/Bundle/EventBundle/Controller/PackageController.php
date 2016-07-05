<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Query\Package\PackageViewQuery;
use Proximum\Vimeet\Application\Command\Package\Step;
use Proximum\Vimeet\Application\Query\Package\Summary\SummaryViewQuery;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Package\Funnel\Step as FunnelStep;
use Proximum\Vimeet\Domain\Package\Summary\TermsOfSale;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\OptionsType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\ParticipantAndPlanningType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\PlansType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\Summary\TermsOfSaleType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PackageController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     *
     * @return RedirectResponse
     */
    public function redirectAction(Request $request, EventDomain $eventDomain)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        try {
            $sheet = $this->get('sheet.sheet_guesser')->getUserSheet($this->getUser(), $eventDomain->getEvent(), $request->getLocale());
        } catch (\Exception $exception) {
            throw $this->createNotFoundException($exception->getMessage());
        }

        return $this->redirectToRoute('event_package_step', [
            'sheet' => $sheet->getId(),
            'step'  => 1,
        ]);
    }

    /**
     * @param Request   $request
     * @param EventDomain $eventDomain
     * @param Sheet     $sheet
     * @param int       $step
     *
     * @return RedirectResponse|Response
     */
    public function stepAction(Request $request, EventDomain $eventDomain, Sheet $sheet, $step)
    {
        $this->authorizeAccess($eventDomain, $sheet, $this->getUser());

        $funnel = $this->get('package.funnel.funnel_factory')->create($sheet, $request->getLocale());

        if (!$funnel->hasStep($step)) {
            throw $this->createNotFoundException(sprintf('Unkown %s step for package of sheet %s', $step, $sheet->getId()));
        }

        $currentStep = $funnel->getStep($step);

        $uncompletedStep = $funnel->getCurrentUncompletedStep();

        if ($currentStep !== $uncompletedStep
            && false !== $uncompletedStep
            && $currentStep->index > $uncompletedStep->index
        ) {
            return $this->redirectToRoute(
                'event_package_step',
                [
                    'sheet' => $sheet->getId(),
                    'step'  => $uncompletedStep->index,
                ]
            );
        }

        $commandClass = $this->stepTypeAssociatedCommand($currentStep->type);
        $command      = new $commandClass($sheet, $currentStep->index);
        $this->assignProductsToCommand($command);

        $form = $this->createForm($this->stepTypeAssociatedForm($currentStep->type), $command, [
            'action' => $this->generateUrl('event_package_step', ['sheet' => $sheet->getId(), 'step' => $step]),
            'sheet'  => $sheet,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);

            $nextStep = $funnel->getNextStep($step);

            if ($nextStep) {
                return $this->redirectToRoute(
                    'event_package_step',
                    ['sheet' => $sheet->getId(), 'step' => $nextStep->index]
                );
            }

            return $this->redirectToRoute('event_package_summary', [
                'sheet' => $sheet->getId(),
            ]);
        }

        $view = $this->get('tactician.commandbus.query')->handle(
            new PackageViewQuery(
                $funnel,
                $currentStep,
                $sheet,
                $request->getLocale()
            )
        );

        return $this->render('EventBundle:Package:step.html.twig', [
            'event' => $eventDomain->getEvent(),
            'view'  => $view,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param $type
     * @return Step\AbstractStep
     * @throws \Exception
     */
    private static function stepTypeAssociatedCommand($type)
    {
        $commands = [
            FunnelStep::TYPE_PLAN                 => Step\SelectPlan::class,
            FunnelStep::TYPE_PARTICIPANT_PLANNING => Step\SelectParticipantAndPlanning::class,
            FunnelStep::TYPE_OPTIONS              => Step\SelectOptions::class,
        ];

        if (isset($commands[$type])) {
            return $commands[$type];
        } else {
            throw new \Exception(sprintf('Command Package Step type %s not implemented', $type));
        }
    }

    /**
     * @param $type
     * @return AbstractType
     *
     * @throws \Exception
     */
    private static function stepTypeAssociatedForm($type)
    {
        $forms = [
            FunnelStep::TYPE_PLAN                 => PlansType::class,
            FunnelStep::TYPE_PARTICIPANT_PLANNING => ParticipantAndPlanningType::class,
            FunnelStep::TYPE_OPTIONS              => OptionsType::class,
        ];

        if (isset($forms[$type])) {
            return $forms[$type];
        } else {
            throw new \Exception(sprintf('Form Package Step type %s not implemented', $type));
        }
    }

    /**
     * @param Step\AbstractStep $command
     */
    private function assignProductsToCommand(Step\AbstractStep $command)
    {
        $cartManager = $this->get('cart_manager');
        $cart        = $cartManager->getCart($command->sheet, $command->currentStep);

        if ($command instanceof Step\SelectPlan) {
            $selectedPlan = $cart->getPlanRow();

            if (null !== $selectedPlan) {
                $command->plan = $selectedPlan->getProduct();
            }

            return;
        }

        if ($command instanceof Step\SelectParticipantAndPlanning) {
            $planningRow = $cart->getPlanningRow();

            if (null !== $planningRow) {
                $command->planningQuantity = $planningRow->getQuantity();
            }

            return;
        }

        if ($command instanceof Step\SelectOptions) {
            /** @var CartRow[] $optionRows */
            $optionRows = array_combine(
                array_map(
                    function (CartRow $cartRow) {
                        return $cartRow->getProduct()->getId();
                    },
                    $cart->getOptionsRow()->toArray()
                ),
                $cart->getOptionsRow()->toArray()
            );

            $availableOptionsId = array_map(
                function (Product $product) {
                    return $product->getId();
                },
                $command->sheet->getPackage()->getAvailablesOptions(new \DateTime())
            );

            $options = [];

            foreach ($availableOptionsId as $optionId) {
                $options[$optionId] = isset($optionRows[$optionId]) ? $optionRows[$optionId]->getQuantity() : 0;
            }

            $command->options = $options;
        }
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return RedirectResponse|Response
     */
    public function summaryAction(Request $request, EventDomain $eventDomain, Sheet $sheet)
    {
        $this->authorizeAccess($eventDomain, $sheet, $this->getUser());

        $funnel = $this->get('package.funnel.funnel_factory')->create($sheet, $request->getLocale());

        if (!$funnel->isCompleted()) {
            return $this->redirectToRoute('event_package_step', [
                'sheet' => $sheet->getId(),
                'step'  => 1,
            ]);
        }

        $billingInfo = $this->get('repository.billing_info_repository')->getBySheet($sheet);

        // Redirect to the billing info action if the billing info are not completed
        if (null === $billingInfo || !$billingInfo->isCompleted()) {
            $this->addFlash('package_complete_billing_info', $sheet->getId());

            return $this->redirectToRoute('event_billing_info', [
                'sheet' => $sheet->getId(),
            ]);
        }

        $termsOfSale = new TermsOfSale();
        $form        = $this->createForm(TermsOfSaleType::class, $termsOfSale);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('event_sheet');
        }

        $view = $this->get('tactician.commandbus.query')->handle(
            new SummaryViewQuery(
                $sheet,
                $funnel,
                $funnel->getCart(),
                $request->getLocale()
            )
        );

        return $this->render('EventBundle:Package:summary.html.twig', [
            'event' => $eventDomain->getEvent(),
            'form'  => $form->createView(),
            'sheet' => $sheet,
            'view'  => $view,
        ]);
    }

    /**
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return RedirectResponse
     */
    public function fillBillingInfoAction(EventDomain $eventDomain, Sheet $sheet)
    {
        $this->authorizeAccess($eventDomain, $sheet, $this->getUser());

        $this->addFlash('package_complete_billing_info', $sheet->getId());

        return $this->redirectToRoute('event_billing_info', [
            'sheet' => $sheet->getId(),
        ]);
    }

    /**
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param User        $user
     */
    private function authorizeAccess(EventDomain $eventDomain, Sheet $sheet, User $user = null)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($sheet->getEvent() !== $eventDomain->getEvent()) {
            throw $this->createNotFoundException(
                sprintf('Sheet %s not present on Event %s')
            );
        }

        if (!$sheet->getPackage()->isPassable()) {
            throw $this->createNotFoundException(sprintf('Package for sheet %s is not passable', $sheet->getId()));
        }

        if (!$sheet->hasUser($user)) {
            throw $this->createNotFoundException(sprintf('The user %s is not participant on the sheet %s', $user->getId(), $sheet->getId()));
        }
    }
}
