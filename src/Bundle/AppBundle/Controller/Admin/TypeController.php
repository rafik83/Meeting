<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Application\Command\Type\Create;
use Proximum\Vimeet\Application\Command\Type\Update;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Type\CreateType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Type\UpdateType;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TypeController extends Controller
{
    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function listAction(Request $request, Event $event)
    {
        $types = $this
            ->get('vimeet_infrastructure.repository.type_repository')
            ->paginate($request->query->get('page', 1), 20, $request->getLocale());

        return $this->render('VimeetAppBundle:Admin/Type:list.html.twig', [
            'event' => $event,
            'types' => $types,
        ]);
    }

    /**
     * @param Request $request
     * @param Event   $event
     *
     * @return Response
     */
    public function createAction(Request $request, Event $event)
    {
        $create = new Create($event);
        $form   = $this->createForm(new CreateType(), $create, [
            'action' => $this->generateUrl('admin_type_create', ['id' => $event->getId()]),
            'method' => 'POST',
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.type.create_handler')->handle($create);
            $this->addFlash('success', 'flash.admin.type.create.success');

            return $this->redirectToRoute('admin_type_update', [
                'id'      => $event->getId(),
                'type_id' => $create->type->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Admin/Type:update.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @ParamConverter(
     *   "type",
     *   class="Proximum\Vimeet\Domain\Model\Type",
     *   options={"id" = "type_id"}
     * )
     *
     * @param Request $request
     * @param Event   $event
     * @param Type    $type
     *
     * @return RedirectResponse|Response
     */
    public function updateAction(Request $request, Event $event, Type $type)
    {
        $update = new Update($type);
        $form   = $this->createForm(new UpdateType(), $update, [
            'action' => $this->generateUrl('admin_type_update', ['id' => $event->getId(), 'type_id' => $type->getId()]),
            'method' => 'POST',
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.type.update_handler')->handle($update);
            $this->addFlash('success', 'flash.admin.type.update.success');

            return $this->redirectToRoute('admin_type_update', [
                'id'      => $event->getId(),
                'type_id' => $type->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Admin/Type:update.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }
}
