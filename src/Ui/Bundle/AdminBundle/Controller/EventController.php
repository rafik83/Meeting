<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Event\BillingConfiguration;
use Proximum\Vimeet\Application\Command\Event\ConfigureDates;
use Proximum\Vimeet\Application\Command\Event\Create;
use Proximum\Vimeet\Application\Command\Event\PaymentConditions\Update as PaymentConditionsUpdate;
use Proximum\Vimeet\Application\Command\Event\PracticalInfo\Update as PracticalInfoUpdate;
use Proximum\Vimeet\Application\Command\Event\Update as EventUpdate;
use Proximum\Vimeet\Application\Command\Order\Find;
use Proximum\Vimeet\Application\Command\Order\FindResult;
use Proximum\Vimeet\Application\Exception\Asset\GuidelineAssetBuildFailedException;
use Proximum\Vimeet\Application\Exception\Event\DomainAlreadyUsedException;
use Proximum\Vimeet\Application\Exception\Order\InvalidNumeroOrderException;
use Proximum\Vimeet\Application\Exception\Order\OrderNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\BillingConfigurationType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\ConfigureDatesType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\PaymentConditions;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\PracticalInfo;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\UpdateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Order\FindType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EventController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        /** @var Admin $admin */
        $admin  = $this->getUser();
        $events = $this
            ->get('vimeet_infrastructure.repository.event_repository')
            ->getListByAdmin($admin);

        $orderForm       = null;
        $formIsSubmitted = false;

        if (!$admin->isPartner()) {
            $find      = new Find($admin);
            $orderForm = $this->createForm(FindType::class, $find);

            $formIsSubmitted = $orderForm->handleRequest($request)->isSubmitted();

            if ($formIsSubmitted && $orderForm->isValid()) {
                try {
                    /** @var FindResult $result */
                    $result = $this->get('tactician.commandbus')->handle($find);

                    return $this->redirect($this->generateUrl('admin_sheet_details', [
                        'event' => $result->sheet->getEvent()->getId(),
                        'sheet' => $result->sheet->getId(),
                    ]) . '#sheetOrders');
                } catch (OrderNotFoundException $exception) {
                    $orderForm->get('numero')->addError(
                        new FormError(
                            $this->get('translator')->trans(
                                'validators.order.orderNotFound',
                                [],
                                'validators'
                            )
                        )
                    );
                } catch (InvalidNumeroOrderException $exception) {
                    $orderForm->get('numero')->addError(
                        new FormError(
                            $this->get('translator')->trans(
                                'validators.order.numeroNotValid',
                                [],
                                'validators'
                            )
                        )
                    );
                }
            }
        }

        return $this->render('AdminBundle:Event:list.html.twig', [
            'events'         => $events,
            'orderForm'      => $orderForm !== null ? $orderForm->createView() : null,
            'orderTabActive' => $orderForm !== null && $formIsSubmitted ? !$orderForm->isValid() : false,
        ]);
    }

    /**
     * @param Event $event
     *
     * @return Response
     */
    public function readAction(Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        return $this->render('AdminBundle:Event:read.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ORGANIZE');

        $create = new Create($this->getUser());

        $form = $this->createForm(CreateType::class, $create, [
            'currentLocale' => $request->getLocale(),
            'submit'        => true,
            'action'        => $this->generateUrl('admin_event_create'),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($create);
                $this->addFlash('success', 'flash.admin.event.create.success');

                return $this->redirectToRoute('admin_event_list');
            } catch (GuidelineAssetBuildFailedException $ex) {
                $this->addFlash('error', 'flash.admin.event.update.asset.failed');
            } catch (DomainAlreadyUsedException $ex) {
                $form->get('domain')->addError(
                    new FormError($this->get('translator')->trans('validators.event.domain.unique', [], 'validators'))
                );
            }
        }

        return $this->render('AdminBundle:Event:create.html.twig', [
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $update = new EventUpdate($event);

        $form = $this->createForm(UpdateType::class, $update, [
            'locales'       => $event->getLocales(),
            'currentLocale' => $request->getLocale(),
            'submit'        => true,
            'action'        => $this->generateUrl('admin_event_update', ['event' => $event->getId()]),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($update);
                $this->addFlash('success', 'flash.admin.event.update.success');

                return $this->redirectToRoute('admin_event_update', ['event' => $event->getId()]);
            } catch (GuidelineAssetBuildFailedException $ex) {
                $this->addFlash('error', 'flash.admin.event.update.asset.failed');
            } catch (DomainAlreadyUsedException $ex) {
                $form->get('domain')->addError(
                    new FormError($this->get('translator')->trans('validators.event.domain.unique', [], 'validators'))
                );
            }
        }

        return $this->render('AdminBundle:Event:update.html.twig', [
            'form'  => $form->createView(),
            'event' => $event,
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function billingConfigurationAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $billingConfiguration = new BillingConfiguration($event);
        $form = $this->createForm(BillingConfigurationType::class, $billingConfiguration, [
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($billingConfiguration);
            $this->addFlash('success', 'flash.admin.event.billing.configuration.success');

            return $this->redirectToRoute('admin_event_list');
        }

        return $this->render('AdminBundle:Event:billingConfiguration.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function practicalInfoAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $update = new PracticalInfoUpdate($event);

        $form = $this->createForm(PracticalInfo\UpdateType::class, $update, [
            'method' => 'POST',
            'event'  => $event,
            'action' => $this->generateUrl('admin_event_practical_info_update', ['event' => $event->getId()]),
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($update);
            $this->addFlash('success', 'flash.admin.event.practicalInfo.update.success');

            return $this->redirectToRoute('admin_event_practical_info_update', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Event/PracticalInfo:update.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function paymentConditionsAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $update = new PaymentConditionsUpdate($event);

        $form = $this->createForm(PaymentConditions\UpdateType::class, $update, [
            'action' => $this->generateUrl('admin_event_payment_conditions', ['event' => $event->getId()]),
            'event'  => $event,
            'submit' => true,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {

            $this->get('tactician.commandbus')->handle($update);
            $this->addFlash('success', 'flash.admin.event.paymentConditions.update.success');

            return $this->redirectToRoute('admin_event_payment_conditions', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Event/PaymentConditions:update.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function datesAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $command = new ConfigureDates($event);
        $form    = $this->createForm(ConfigureDatesType::class, $command, ['event' => $event, 'submit' => true]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($command);
            $this->addFlash('success', 'flash.admin.event.configure_dates.success');

            return $this->redirectToRoute('admin_event_configure_dates', ['event' => $event->getId()]);
        }

        return $this->render('AdminBundle:Event:dates.html.twig', [
            'form'  => $form->createView(),
            'event' => $event,
        ]);
    }
}
