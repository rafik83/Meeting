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
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Participant\ParticipantCreateType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Participant\ParticipantUpdateType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\RegisterType;
use Proximum\Vimeet\Domain\Model\EventView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\TypeView;
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
     * @param EventView $eventView
     *
     * @return Response
     */
    public function indexAction(Request $request, EventView $eventView)
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $sheets = $this
                ->get('vimeet_infrastructure.repository.sheet_repository')
                ->getSheetsIdByUserAndEvent($this->getUser()->getId(), $eventView->id, $request->getLocale());
        } else {
            $sheets = [];
        }

        $typeViews = $this
            ->get('vimeet_infrastructure.repository.type_repository')
            ->getTypeViewsByEvent($eventView->id, $request->getLocale());

        return $this->render('VimeetAppBundle:Event:index.html.twig', [
            'event'  => $eventView,
            'types'  => $typeViews,
            'sheets' => $sheets,
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
        // Redirect to participate form if the user is already authenticated
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('event_participate', [
                'typeView'  => $typeView->id,
                'subdomain' => $request->attributes->get('subdomain'),
            ]);
        }

        // Else, create the register form
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
                // Register and authenticate the user
                $this->get('vimeet_infrastructure.application.command.user.register_handler')->handle($register);
                $this->authenticate($register->user);
                $this->addFlash('success', 'flash.event.register.success');

                // Go to participate form
                return $this->redirectToRoute('event_participate', [
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
    public function participateAction(Request $request, EventView $eventView, TypeView $typeView)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Create participate form
        $create = new Create();
        $form   = $this->createForm(new ParticipantCreateType(), $create, [
            'locale'   => $request->getLocale(),
            'template' => $this->get('vimeet_infrastructure.repository.type_repository')->getParticipantTemplate($typeView->id)
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            // Create the participant
            $event       = $this->get('vimeet_infrastructure.repository.event_repository')->getById($eventView->id);
            $type        = $this->get('vimeet_infrastructure.repository.type_repository')->getById($typeView->id);
            $participate = new Participate($this->getUser(), $event, $type, $create->data);

            $this->get('vimeet_infrastructure.application.command.user.participate_handler')->handle($participate);
            $this->addFlash('success', 'flash.event.participation.success');

            // Go to the sheet
            return $this->redirectToRoute('event_sheet', [
                'subdomain' => $request->attributes->get('subdomain'),
                'id'        => $participate->sheet->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Event:participate.html.twig', [
            'form'      => $form->createView(),
            'eventView' => $eventView,
            'typeView'  => $typeView,
        ]);
    }


    /**
     * Sheet
     *
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return RedirectResponse|Response
     */
    public function sheetAction(Request $request, EventView $eventView, Sheet $sheet)
    {
        return $this->render('VimeetAppBundle:Event:sheet.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
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
            'template' => $this->get('vimeet_infrastructure.repository.type_repository')->getParticipantTemplate($participantView->typeId)
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
            ->get('vimeet_infrastructure.repository.type_repository')
            ->getParticipantTemplate($participantView->typeId);

        return $this->render('VimeetAppBundle:Event:participationSummary.html.twig', [
            'participantView' => $participantView,
            'template'        => $template,
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
        if ($this->getUser()->getUsername() !== $participantView->userEmail) {
            throw $this->createAccessDeniedException();
        }

        // Check if the participation is for this event
        if ($eventView->id !== $participantView->eventId) {
            throw $this->createNotFoundException();
        }
    }
}
