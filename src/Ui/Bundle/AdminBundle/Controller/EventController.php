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
use Proximum\Vimeet\Application\Command\Transaction\Filter as FilterTransaction;
use Proximum\Vimeet\Application\Command\Order\FindResult;
use Proximum\Vimeet\Application\Exception\Asset\GuidelineAssetBuildFailedException;
use Proximum\Vimeet\Application\Exception\Event\DomainAlreadyUsedException;
use Proximum\Vimeet\Application\Exception\Order\InvalidNumeroOrderException;
use Proximum\Vimeet\Application\Exception\Order\OrderNotFoundException;
use Proximum\Vimeet\Application\Exception\Transaction\TransactionNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Order\Finder;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\BillingConfigurationType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\ConfigureDatesType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\PaymentConditions;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\PracticalInfo;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\UpdateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Order\FindType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Transaction\FilterType as FilterTransactionType;
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

        $orderForm                = null;
        $formIsSubmitted          = false;
        $transactionForm          = null;

        if (Finder::IsAllowedToFind($admin)) {
            $filterTransaction = new FilterTransaction($admin);
            $transactionForm = $this->createForm(
                FilterTransactionType::class,
                $filterTransaction,
                ['action' => $this->generateUrl('admin.event.transaction_export')]
            );
            
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
            'events'                => $events,
            'orderForm'             => $orderForm !== null ? $orderForm->createView() : null,
            'orderTabActive'        => $orderForm !== null && $formIsSubmitted ? !$orderForm->isValid() : false,
            'transactionForm'       => $transactionForm !== null ? $transactionForm->createView() : null,
        ]);
    }
    
    /**
     * @param Request $request
     *
     * @return CsvFileResponse|RedirectResponse
     */
    public function exportTransactionAction(Request $request)
    {
        $admin = $this->getUser();
        
        if (Finder::IsAllowedToFind($admin)) {
            
            $filterTransaction = new FilterTransaction($admin);
            $transactionForm = $this->createForm(FilterTransactionType::class, $filterTransaction);
    
            if ($transactionForm->handleRequest($request)->isSubmitted() && $transactionForm->isValid()) {
                try {
                    $transactionListView = $this->get('tactician.commandbus')->handle($filterTransaction);
                    
                    $filePath = $this->get('query.transaction.list_view_query_handler')->handle($transactionListView);
                    
                    return new CsvFileResponse(
                        file_get_contents($this->getParameter('infrastructure.export_transactions_path') . $filePath),
                        sprintf('export_transactions_%s.csv', date("Y_m_d_His"))
                    );
                } catch (TransactionNotFoundException $exception) {
                    dump($exception);die;
                }
            }
        }
        
        return $this->redirectToRoute('admin_event_list');
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
            'currentLocale' => $event->getAvailableLocale($request->getLocale()),
            'event'         => $event,
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
