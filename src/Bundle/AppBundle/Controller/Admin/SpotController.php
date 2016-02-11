<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Spot\BatchCreate;
use Proximum\Vimeet\Application\Command\Spot\Create;
use Proximum\Vimeet\Application\Exception\Spot\MultipleUniqueReferenceViolationException;
use Proximum\Vimeet\Application\Exception\Spot\UniqueReferenceViolationException;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Spot\BatchCreateType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Spot\SpotCreateType;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SpotController extends Controller
{
    /**
     * @param Event $event
     *
     * @return Response
     */
    public function listAction(Event $event)
    {
        $spots = $this
            ->get('vimeet_infrastructure.repository.spot_repository')
            ->getByEvent($event);

        return $this->render('VimeetAppBundle:Admin/Spot:list.html.twig', [
            'spots' => $spots,
            'event' => $event,
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return RedirectResponse|Response
     */
    public function createAction(Request $request, Event $event)
    {
        $create = new Create($event);
        $form   = $this->createForm(SpotCreateType::class, $create, [
            'action' => $this->generateUrl('admin_spot_create', ['event' => $event->getId()]),
            'method' => 'POST',
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('vimeet_infrastructure.vimeet.application.command.spot.create_handler')->handle($create);
                $this->addFlash('success', 'flash.admin.spot.create.success');

                return $this->redirectToRoute('admin_spot_list', ['event' => $event->getId()]);
            } catch (UniqueReferenceViolationException $exception) {
                $form->get('reference')->addError(new FormError($this->get('translator')->trans('validators.spot.reference.unique', [], 'validators')));
            }
        }

        return $this->render('VimeetAppBundle:Admin/Spot:create.html.twig', [
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
    public function batchCreateAction(Request $request, Event $event)
    {
        $batchCreate = new BatchCreate($event);
        $form        = $this->createForm(BatchCreateType::class, $batchCreate);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('command.spot.batch_create_handler')->handle($batchCreate);
                $this->addFlash('success', 'flash.admin.spot.batch_create.success');
            } catch (MultipleUniqueReferenceViolationException $exception) {
                $this->addFlash('warning', [
                    'message'   => 'flash.admin.spot.batch_create.duplicate',
                    'arguments' => ['%references%' => $exception->getReferencesAsString()],
                ]);
            }

            return $this->redirectToRoute('admin_spot_list', ['event' => $event->getId()]);
        }

        return $this->render('VimeetAppBundle:Admin/Spot:batchCreate.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
