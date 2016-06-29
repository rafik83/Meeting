<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Event\Create;
use Proximum\Vimeet\Application\Command\Event\Update as EventUpdate;
use Proximum\Vimeet\Application\Command\Event\PracticalInfo\Update as PracticalInfoUpdate;
use Proximum\Vimeet\Application\Command\Event\PaymentConditions\Update as PaymentConditionsUpdate;
use Proximum\Vimeet\Application\Exception\Asset\GuidelineAssetBuildFailedException;
use Proximum\Vimeet\Application\Exception\Event\DomainAlreadyUsedException;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\CreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\PaymentConditions;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\UpdateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\PracticalInfo;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EventController extends Controller
{
    /**
     * @return Response
     */
    public function listAction()
    {
        $events = $this
            ->get('vimeet_infrastructure.repository.event_repository')
            ->getListByAdmin($this->getUser());


        return $this->render('AdminBundle:Event:list.html.twig', [
            'events' => $events,
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
     * @return RedirectResponse|Response
     */
    public function practicalInfoAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $update = new PracticalInfoUpdate($event);

        $form = $this->createForm(
            PracticalInfo\UpdateType::class,
            $update,
            [
                'method' => 'POST',
                'event'  => $event,
                'action' => $this->generateUrl('admin_event_practical_info_update', ['event' => $event->getId()])
            ]
        );
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('tactician.commandbus')->handle($update);
            $this->addFlash('success', 'flash.admin.event.practicalInfo.update.success');

            return $this->redirectToRoute('admin_event_practical_info_update', ['event' => $event->getId()]);
        }

        return $this->render(
            'AdminBundle:Event/PracticalInfo:update.html.twig',
            [
                'event' => $event,
                'form'  => $form->createView(),
            ]
        );
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
}
