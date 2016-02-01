<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Event;

use Proximum\Vimeet\Application\Command\Participant\Create;
use Proximum\Vimeet\Application\Command\User\Participate;
use Proximum\Vimeet\Application\Command\User\Register;
use Proximum\Vimeet\Application\Exception\Data\RequiredDataEmptyException;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Participant\ParticipantCreateType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\RegisterType;
use Proximum\Vimeet\Domain\View\EventView;
use Proximum\Vimeet\Domain\View\TypeView;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class HomeController extends BaseController
{
    /**
     * Event home.
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
                ->getSheetViewsByUserAndEvent($this->getUser()->getId(), $eventView->id, $request->getLocale());
        } else {
            $sheets = [];
        }

        $typeViews = $this
            ->get('vimeet_infrastructure.repository.type_repository')
            ->getTypeViewsByEvent($eventView->id, $eventView->locale);

        return $this->render('VimeetAppBundle:Event/Home:index.html.twig', [
            'eventView' => $eventView,
            'types'     => $typeViews,
            'sheets'    => $sheets,
        ]);
    }

    /**
     * Register an account.
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
            return $this->redirectToRoute('event_participate', ['typeView' => $typeView->id]);
        }

        // Else, create the register form
        $register         = new Register();
        $register->locale = $request->getLocale();

        $form = $this->createForm(RegisterType::class, $register, [
            'action' => $this->generateUrl('event_register', ['typeView'  => $typeView->id]),
            'method' => 'POST',
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                // Register and authenticate the user
                $this->get('vimeet_infrastructure.application.command.user.register_handler')->handle($register);
                $this->authenticate($register->user);
                $this->addFlash('success', 'flash.event.register.success');

                // Go to participate form
                return $this->redirectToRoute('event_participate', ['typeView'  => $typeView->id]);
            } catch (EmailAlreadyExistsException $exception) {
                $error = new FormError($this->get('translator')->trans('register.email_already_exists'));
                $form->get('email')->addError($error);
            }
        }

        return $this->render('VimeetAppBundle:Event/Home:register.html.twig', [
            'form'      => $form->createView(),
            'eventView' => $eventView,
            'typeView'  => $typeView,
        ]);
    }

    /**
     * Create a participation.
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

        // Check if the user has already created a participate
        $this->hasUserAlreadyCreatedParticipant($this->getUser()->getId());

        // Create participate form
        $create = new Create();
        $form   = $this->createForm(ParticipantCreateType::class, $create, [
            'locale'   => $eventView->locale,
            'template' => $this
                ->get('vimeet_infrastructure.repository.type_repository')
                ->getParticipantTemplate($typeView->id),
        ]);
        $form->add('submit', SubmitType::class);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $event = $this->get('vimeet_infrastructure.repository.event_repository')->getById($eventView->id);
            $type  = $this->get('vimeet_infrastructure.repository.type_repository')->getById($typeView->id);

            try {
                // Create the participant
                $participate = new Participate($this->getUser(), $event, $type, $create->data);

                $this
                    ->get('vimeet_infrastructure.application.command.user.participate_handler')
                    ->handle($participate);

                $this->addFlash('success', 'flash.event.participation.success');

                // Go to the sheet
                return $this->redirectToRoute('event_sheet', ['id' => $participate->sheet->getId()]);
            } catch (RequiredDataEmptyException $exception) {
                $form = $this->addRequiredErrorOnForm($form, $type->getParticipantTemplate(), $create->data, $form->get('data'));
            }
        }

        return $this->render('VimeetAppBundle:Event/Home:participate.html.twig', [
            'form'      => $form->createView(),
            'eventView' => $eventView,
            'typeView'  => $typeView,
        ]);
    }

    /**
     * @param int $userId
     */
    private function hasUserAlreadyCreatedParticipant($userId)
    {
        $participants = $this
            ->get('vimeet_infrastructure.repository.participant_repository')
            ->getAllParticipantForUser($userId);

        if (1 <= count($participants)) {
            throw new AccessDeniedException('Participation already created');
        }
    }
}
