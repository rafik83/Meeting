<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Application\Command\Type\Create;
use Proximum\Vimeet\Application\Command\Type\Update;
use Proximum\Vimeet\Application\Exception\Type\TypeAlreadyExistsException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\TypeCreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Type\TypeUpdateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TypeController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function createAction(Request $request, Event $event)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        $create = new Create($event, $event->getAvailableLocale($request->getLocale()));
        $form   = $this->createForm(TypeCreateType::class, $create, [
            'action'       => $this->generateUrl('admin_type_create', ['event' => $event->getId()]),
            'method'       => 'POST',
            'events'       => $this->get('vimeet_infrastructure.repository.event_repository')
                                   ->getListByAdmin($this->getUser()),
            'currentEvent' => $event,
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($create);
                $this->addFlash('success', 'flash.admin.type.create.success');

                return $this->redirectToRoute('admin_type_list', ['event' => $event->getId()]);
            } catch (TypeAlreadyExistsException $typeAlreadyExistsException) {
                $error = new FormError($this->get('translator')->trans('admin.type.already_exists'));

                foreach ($typeAlreadyExistsException->getLocales() as $locale) {
                    $form->get('translations')->get($locale)->get('title')->addError($error);
                }
            }
        }

        return $this->render('AdminBundle:Type:create.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     * @param Type    $type
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, Event $event, Type $type)
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);

        if ($event !== $type->getEvent()) {
            throw $this->createNotFoundException('Type not found.');
        }

        $update = new Update($type, $event->getAvailableLocale($request->getLocale()));
        $form   = $this->createForm(TypeUpdateType::class, $update, [
            'action'       => $this->generateUrl('admin_type_update',
                ['event' => $event->getId(), 'type' => $type->getId()]),
            'method'       => 'POST',
            'events'       => $this->get('vimeet_infrastructure.repository.event_repository')
                                   ->getListByAdmin($this->getUser()),
            'currentEvent' => $event,
            'type'         => $type,
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($update);
                $this->addFlash('success', 'flash.admin.type.update.success');

                return $this->redirectToRoute('admin_type_list', ['event' => $event->getId()]);
            } catch (TypeAlreadyExistsException $typeAlreadyExistsException) {
                $error = new FormError($this->get('translator')->trans('admin.type.already_exists'));

                foreach ($typeAlreadyExistsException->getLocales() as $locale) {
                    $form->get('translations')->get($locale)->get('title')->addError($error);
                }
            }
        }

        return $this->render('AdminBundle:Type:update.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
