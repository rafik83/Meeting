<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Participant\Add;
use Proximum\Vimeet\Application\Command\Participant\Remove;
use Proximum\Vimeet\Application\Exception\Participant\AlreadyLinkedToASheetOfThisEventException;
use Proximum\Vimeet\Application\Exception\Participant\CanNotRemoveAllParticipantsException;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Application\Query\Package\PackageViewQuery;
use Proximum\Vimeet\Application\Command\Package\Step;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Domain\Model\CartRow;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Funnel\Step as FunnelStep;
use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\OptionsType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\ParticipantAndPlanningType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package\PlansType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\AddType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\RemoveType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormError;
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

        $currentStep = $funnel->getStep($step);

        $uncompletedStep = $funnel->getCurrentUncompletedStep();

        if ($currentStep !== $uncompletedStep
            && null !== $uncompletedStep
            && FunnelStep::TYPE_PLAN === $uncompletedStep->type
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
        $command      = new $commandClass($sheet);
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

            return $this->redirectToRoute('event_sheet');
        }

        $displayAddParticipantForm    = false;
        $displayRemoveParticipantForm = false;
        $form_add                     = null;
        $form_remove                  = null;
        $participants                 = [];

        if ($currentStep->type === FunnelStep::TYPE_PARTICIPANT_PLANNING) {
            list (
                $displayAddParticipantForm,
                $displayRemoveParticipantForm,
                $form_add,
                $form_remove,
                $participants,
                $redirect
            ) = $this->handleStepParticipant($request, $eventDomain, $sheet, $step);

            if ($redirect) {
                return $this->redirectToRoute('event_package_step', [
                    'sheet' => $sheet->getId(),
                    'step'  => $step,
                ]);
            }
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
            'event'                        => $eventDomain->getEvent(),
            'view'                         => $view,
            'form'                         => $form->createView(),
            'form_add'                     => null !== $form_add ? $form_add->createView() : $form_add,
            'form_remove'                  => null !== $form_remove ? $form_remove->createView() : $form_remove,
            'displayAddParticipantForm'    => $displayAddParticipantForm,
            'displayRemoveParticipantForm' => $displayRemoveParticipantForm,
            'participants'                 => $participants,
        ]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param int         $step
     *
     * @return array|RedirectResponse
     */
    private function handleStepParticipant(Request $request, EventDomain $eventDomain, Sheet $sheet, $step)
    {
        $locale = $request->getLocale();
        $displayAddParticipantForm    = false;
        $displayRemoveParticipantForm = false;
        $redirect                     = false;

        $addParticipant = new Add($sheet, $eventDomain->getEvent(), $locale);
        $form_add       = $this->createForm(AddType::class, $addParticipant, [
            'action' => $this->generateUrl('event_package_step', [
                'sheet' => $sheet->getId(),
                'step'  => $step,
            ]),
        ]);

        $removeParticipant = new Remove($sheet);
        $form_remove       = $this->createForm(RemoveType::class, $removeParticipant, [
            'action'       => $this->generateUrl('event_package_step', [
                'sheet' => $sheet->getId(),
                'step'  => $step,
            ]),
            'participants' => $sheet->getParticipants(),
        ]);

        if ($form_add->handleRequest($request)->isSubmitted() && $form_add->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($addParticipant);

                $redirect = true;
            } catch (AlreadyLinkedToASheetOfThisEventException $exception) {
                $form_add->get('email')->addError(new FormError('validators.participant.alreadyLinkedToASheet'));
            } catch (ParticipantAlreadyExistException $exception) {
                $form_add->get('email')->addError(new FormError('validators.participant.alreadyLinkedToThisSheet'));
            }

            $displayAddParticipantForm = true;
        }

        if ($form_remove->handleRequest($request)->isSubmitted() && $form_remove->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($removeParticipant);

                $redirect = true;
            } catch (CanNotRemoveAllParticipantsException $exception) {
                $form_remove->addError(new FormError('validators.participant.canNotRemoveAllParticipants'));
            }

            $displayRemoveParticipantForm = true;
        }

        $cardListViewQuery = new CardListViewQuery($sheet, $this->getUser(), $locale, false);
        $participants      = $this->get('tactician.commandbus.query')->handle($cardListViewQuery);

        return [
            $displayAddParticipantForm,
            $displayRemoveParticipantForm,
            $form_add,
            $form_remove,
            $participants,
            $redirect,
        ];
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param int         $step
     *
     * @return Response
     */
    public function addParticipantAction(Request $request, EventDomain $eventDomain, Sheet $sheet, $step)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!$sheet->hasUser($this->getUser())) {
            throw $this->createNotFoundException(
                sprintf(
                    'The current user %s is not associated with this sheet %s',
                    $this->getUser()->getId(),
                    $sheet->getId()
                )
            );
        }

        $locale         = $request->getLocale();
        $label          = $sheet->getPackage()->getParticipant()->getTitle($locale);
        $addParticipant = new Add($sheet, $eventDomain->getEvent(), $locale);
        $form           = $this->createForm(AddType::class, $addParticipant, [
            'action' => $this->generateUrl('event_package_step', [
                'sheet' => $sheet->getId(),
                'step'  => $step,
            ]),
        ]);

        return $this->render('EventBundle:Participant:addFromPackage.html.twig', [
            'label' => $label,
            'form'  => $form->createView(),
        ]);
    }


    /**
     * @param Request $request
     * @param Sheet   $sheet
     * @param int     $step
     *
     * @return Response
     */
    public function removeParticipantAction(Request $request, Sheet $sheet, $step)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if (!$sheet->hasUser($this->getUser())) {
            throw $this->createNotFoundException(
                sprintf(
                    'The current user %s is not associated with this sheet %s',
                    $this->getUser()->getId(),
                    $sheet->getId()
                )
            );
        }

        if ($sheet->countParticipants() === 1) {
            throw $this->createNotFoundException('Impossible to remove participants from a sheet with one participant');
        }

        $remove = new Remove($sheet);
        $form   = $this->createForm(RemoveType::class, $remove, [
            'action'       => $this->generateUrl('event_package_step', [
                'sheet' => $sheet->getId(),
                'step'  => $step,
            ]),
            'participants' => $sheet->getParticipants(),
        ]);

        $locale            = $request->getLocale();
        $label             = $sheet->getPackage()->getParticipant()->getTitle($locale);
        $cardListViewQuery = new CardListViewQuery($sheet, $this->getUser(), $locale, false);
        $participants      = $this->get('tactician.commandbus.query')->handle($cardListViewQuery);

        return $this->render('EventBundle:Participant:removeFromPackage.html.twig', [
            'form'         => $form->createView(),
            'label'        => $label,
            'participants' => $participants,
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
        $cart        = $cartManager->getCart($command->sheet);

        if ($command instanceof Step\SelectPlan) {
            $selectedPlan = $cart->getPlanRow();

            if ($selectedPlan) {
                $command->plan = $selectedPlan->getProduct();
            }

            return;
        }

        if ($command instanceof Step\SelectParticipantAndPlanning) {
            $planningRow = $cart->getPlanningRow();

            if ($planningRow) {
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
                $command->sheet->getPackage()->getAvailablesOptions()
            );

            $options = [];

            foreach ($availableOptionsId as $optionId) {
                $options[$optionId] = isset($optionRows[$optionId]) ? $optionRows[$optionId]->getQuantity() : 0;
            }

            $command->options = $options;
        }
    }
}
