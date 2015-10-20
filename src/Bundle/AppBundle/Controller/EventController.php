<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller;

use Proximum\Vimeet\Application\Command\Participant\Create;
use Proximum\Vimeet\Application\Command\Participant\Update;
use Proximum\Vimeet\Application\Command\User\Participate;
use Proximum\Vimeet\Application\Command\User\Register;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\ParticipantCreateType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\ParticipantUpdateType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\RegisterType;
use Proximum\Vimeet\Domain\Model\EventView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Participant\TypeView;
use Proximum\Vimeet\Domain\Model\ParticipantView;
use Proximum\Vimeet\Domain\Model\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class EventController extends Controller
{
    /**
     * Event home
     *
     * @param Request   $request
     * @param EventView $event
     *
     * @return Response
     */
    public function indexAction(Request $request, EventView $event)
    {
        $participantId = null;

        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $participantId = $this
                ->get('vimeet_infrastructure.repository.participant_repository')
                ->getLastParticipantIdForEventAndUser($this->getUser()->getId(), $event->id);
        }

        $participantTypes = $this
            ->get('vimeet_infrastructure.repository.participant.type_repository')
            ->getTypeViewsByEvent($event->id, $request->getLocale());

        return $this->render('VimeetAppBundle:Event:index.html.twig', [
            'event'             => $event,
            'participant_types' => $participantTypes,
            'participantId'     => $participantId,
        ]);
    }

    /**
     * Register an account
     *
     * @param Request   $request
     * @param EventView $eventView
     * @param TypeView  $typeView
     *
     * @return RedirectResponse|Response
     */
    public function registerAction(Request $request, EventView $eventView, TypeView $typeView)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('event_participation', [
                'typeView'  => $typeView->id,
                'subdomain' => $request->attributes->get('subdomain'),
            ]);
        }

        $register = new Register();
        $register->locale = $request->getLocale();

        $form = $this->createForm(new RegisterType(), $register, [
            'action' => $this->generateUrl('event_register', [
                'typeView'  => $typeView->id,
                'subdomain' => $request->attributes->get('subdomain'),
            ]),
            'method' => 'POST',
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('vimeet_infrastructure.application.command.user.register_handler')->handle($register);
                $this->authenticate($register->user);
                $this->addFlash('success', 'flash.event.register.success');

                return $this->redirectToRoute('event_participation', [
                    'typeView'  => $typeView->id,
                    'subdomain' => $request->attributes->get('subdomain'),
                ]);
            } catch (EmailAlreadyExistsException $exception) {
                $error = new FormError($this->get('translator')->trans('messages.register.email_already_exists'));
                $form->get('email')->addError($error);
            }
        }

        return $this->render('VimeetAppBundle:Event:register.html.twig', [
            'form'      => $form->createView(),
            'eventView' => $eventView,
            'typeView'  => $typeView,
        ]);
    }

    /**
     * Create a participation
     *
     * @param Request   $request
     * @param EventView $eventView
     * @param TypeView  $typeView
     *
     * @return RedirectResponse|Response
     */
    public function participationAction(Request $request, EventView $eventView, TypeView $typeView)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $create = new Create();
        $form   = $this->createForm(new ParticipantCreateType(), $create, [
            'locale'   => $request->getLocale(),
            'template' => $this->get('vimeet_infrastructure.repository.form_repository')->getTemplate($typeView->id)
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $event       = $this->get('vimeet_infrastructure.repository.event_repository')->getById($eventView->id);
            $type        = $this->get('vimeet_infrastructure.repository.participant.type_repository')->getById($typeView->id);
            $participate = new Participate($this->getUser(), $event, $type, $create->data);

            $this->get('vimeet_infrastructure.application.command.user.participate_handler')->handle($participate);
            $this->addFlash('success', 'flash.event.participation.success');

            return $this->redirectToRoute('event_participation_summary', [
                'subdomain'       => $request->attributes->get('subdomain'),
                'participantView' => $participate->participant->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Event:participation.html.twig', [
            'form'      => $form->createView(),
            'eventView' => $eventView,
            'typeView'  => $typeView,
        ]);
    }

    /**
     * Edit a participation
     *
     * @param Request         $request
     * @param EventView       $eventView
     * @param ParticipantView $participantView
     *
     * @return RedirectResponse|Response
     */
    public function participationUpdateAction(Request $request, EventView $eventView, ParticipantView $participantView)
    {
        $this->checkParticipantAccess($eventView, $participantView);

        $update = new Update($participantView->id, $participantView->data);
        $form   = $this->createForm(new ParticipantUpdateType(), $update, [
            'locale'   => $request->getLocale(),
            'template' => $this->get('vimeet_infrastructure.repository.form_repository')->getTemplate($participantView->typeId)
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->get('vimeet_infrastructure.vimeet.application.command.participant.update_handler')->handle($update);
            $this->addFlash('success', 'flash.event.participation.update.success');

            return $this->redirectToRoute('event_participation_update', [
                'subdomain'       => $request->attributes->get('subdomain'),
                'participantView' => $participantView->id,
            ]);
        }

        return $this->render('VimeetAppBundle:Event:participationUpdate.html.twig', [
            'form'            => $form->createView(),
            'eventView'       => $eventView,
            'participantView' => $participantView,
        ]);
    }

    /**
     * Participation summary
     *
     * @param EventView       $eventView
     * @param ParticipantView $participantView
     *
     * @return Response
     */
    public function participationSummaryAction(EventView $eventView, ParticipantView $participantView)
    {
        $this->checkParticipantAccess($eventView, $participantView);

        $template = $this
            ->get('vimeet_infrastructure.repository.form_repository')
            ->getTemplate($participantView->typeId);

        return $this->render('VimeetAppBundle:Event:participationSummary.html.twig', [
            'participantView' => $participantView,
            'template'        => json_decode($template, true),
        ]);
    }

    /**
     * Authenticate user
     *
     * @param User $user
     */
    private function authenticate(User $user)
    {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
    }

    /**
     * @param EventView       $eventView
     * @param ParticipantView $participantView
     */
    private function checkParticipantAccess(EventView $eventView, ParticipantView $participantView)
    {
        // Check if user is authenticated
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Check if user own participation
        if ($this->getUser() === null || $this->getUser()->getUsername() !== $participantView->userEmail) {
            throw $this->createAccessDeniedException();
        }

        // Check if the participation is for this event
        if ($eventView->id !== $participantView->eventId) {
            throw $this->createNotFoundException();
        }
    }
}
