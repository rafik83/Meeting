<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller;

use Proximum\Vimeet\Application\Command\User\Register;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\RegisterType;
use Proximum\Vimeet\Domain\Model\EventView;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EventController extends Controller
{
    /**
     * @param Request   $request
     * @param EventView $event
     *
     * @return Response
     */
    public function indexAction(Request $request, EventView $event)
    {
        $participantTypes = $this
            ->get('vimeet_infrastructure.repository.participant.type_repository')
            ->getTypeViewByEvent($event->id, $request->getLocale());

        return $this->render('VimeetAppBundle:Event:index.html.twig', [
            'event'             => $event,
            'participant_types' => $participantTypes,
        ]);
    }

    /**
     * @param Request   $request
     * @param EventView $event
     * @param integer   $typeId
     *
     * @return RedirectResponse|Response
     */
    public function registerAction(Request $request, EventView $event, $typeId)
    {
        $register = new Register();

        $form = $this->createForm(new RegisterType(), $register, [
            'action' => $this->generateUrl('event_register', [
                'typeId'    => $typeId,
                'subdomain' => $request->attributes->get('subdomain'),
            ]),
            'method' => 'POST',
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted()) {
            try {
                $this->get('vimeet_infrastructure.application.command.user.register_handler')->handle($register);

                $this->addFlash('success', 'flash.event.register.success');

                return $this->redirectToRoute('event_register', [
                    'typeId'    => $typeId,
                    'subdomain' => $request->attributes->get('subdomain'),
                ]);
            } catch (EmailAlreadyExistsException $exception) {
                $form->addError(new FormError($this->get('translator')->trans('messages.register.email_already_exists')));
            }
        }

        return $this->render('VimeetAppBundle:Event:register.html.twig', [
            'form'  => $form->createView(),
            'event' => $event,
        ]);
    }
}
