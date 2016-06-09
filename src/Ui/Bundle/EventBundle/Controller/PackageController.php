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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Funnel\Step as FunnelStep;
use Proximum\Vimeet\Domain\View\EventView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\OptionsType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\ParticipantAndPlanningType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\PlansType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PackageController extends Controller
{
    /**
     * @param Request   $request
     * @param EventView $eventView
     *
     * @return RedirectResponse
     */
    public function redirectAction(Request $request, EventView $eventView)
    {
        try {
            $sheet = $this->get('sheet.sheet_guesser')->getUserSheet($this->getUser(), $eventView, $request->getLocale());
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
     * @param EventView $eventView
     * @param Sheet     $sheet
     * @param int       $step
     *
     * @return Response
     */
    public function stepAction(Request $request, EventView $eventView, Sheet $sheet, $step)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$sheet->getPackage()->isPassable()) {
            throw $this->createNotFoundException(sprintf('Package for sheet %s is not passable', $sheet->getId()));
        }

        if (!$sheet->hasUser($this->getUser())) {
            throw $this->createNotFoundException(sprintf('The user %s is not participant on the sheet %s', $this->getUser()->getId(), $sheet->getId()));
        }

        $funnel = $this->get('package.funnel.funnel_factory')->create($sheet, $request->getLocale());

        if (!$funnel->hasStep($step)) {
            throw $this->createNotFoundException(sprintf('Unkown %s step for package of sheet %s', $step, $sheet->getId()));
        }

        $currentStep     = $funnel->getStep($step);
        $uncompletedStep = $funnel->getCurrentUncompletedStep();

        if (!$currentStep->completed && null !== $uncompletedStep && $uncompletedStep !== $currentStep) {
            return $this->redirectToRoute(
                'event_package_step',
                [
                    'sheet' => $sheet->getId(),
                    'step'  => $uncompletedStep->index,
                ]
            );
        }

        $commandClass = $this->stepTypeAssociatedCommand($currentStep->type);
        $command      = new $commandClass($sheet);
        $this->assignProductsToCommand($command);
        $form         = $this->createForm($this->stepTypeAssociatedForm($currentStep->type), $command, [
            'action'  => $this->generateUrl('event_package_step', ['sheet' => $sheet->getId(), 'step' => $step]),
            'package' => $sheet->getPackage(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);

            return $this->redirectToRoute('event_package_step', ['sheet' => $sheet->getId(), 'step' => $step]);
        }

        $view = $this->get('tactician.commandbus.query')->handle(
            new PackageViewQuery(
                $funnel,
                $currentStep,
                $this->get('vimeet_infrastructure.repository.event_repository')->getById($eventView->getId()),
                $sheet->getPackage(),
                $request->getLocale()
            )
        );

        return $this->render('EventBundle:Package:step.html.twig', [
            'eventView' => $eventView,
            'view'      => $view,
            'form'      => $form->createView(),
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
            FunnelStep::TYPE_PARTICIPANT_PLANNING => Step\ParticipantAndPlanning::class,
            FunnelStep::TYPE_OPTIONS              => Step\Options::class,
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
        if ($command instanceof Step\SelectPlan) {
            $cartRow = $this->get('repository.cart_row_repository')->findCartRowPlanBySheet($command->sheet);

            if (null !== $cartRow) {
                $command->plan = $cartRow->getProduct();
            }
        }
    }
}
