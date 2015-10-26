<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller;

use Proximum\Vimeet\Application\Command\Participant\Add;
use Proximum\Vimeet\Application\Command\Participant\Create;
use Proximum\Vimeet\Application\Command\Participant\Update;
use Proximum\Vimeet\Application\Command\Participant\Delete;
use Proximum\Vimeet\Application\Command\Participant\DeleteHandler;
use Proximum\Vimeet\Application\Command\Sheet\UpdateBlock;
use Proximum\Vimeet\Application\Command\User\Participate;
use Proximum\Vimeet\Application\Command\User\Register;
use Proximum\Vimeet\Application\Exception\Participant\isNotOwnerException;
use Proximum\Vimeet\Application\Exception\Participant\OwnerCanNotBeDeletedException;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Participant\AddParticipantType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Participant\ParticipantCreateType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Participant\ParticipantUpdateType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\RegisterType;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Sheet\UpdateBlockType;
use Proximum\Vimeet\Domain\Model\EventView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\TypeView;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Specification\Sheet\CanAccess;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
                $error = new FormError($this->get('translator')->trans('register.email_already_exists'));
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

        // Check if the user has already created a participate
        $participants = $this
            ->get('vimeet_infrastructure.repository.participant_repository')
            ->getAllParticipantForUser($this->getUser()->getId());

        if (1 <= count($participants)) {
            throw new AccessDeniedException('Participation already created');
        }

        // Create participate form
        $create = new Create();
        $form   = $this->createForm(new ParticipantCreateType(), $create, [
            'locale'   => $request->getLocale(),
            'template' => $this
                ->get('vimeet_infrastructure.repository.type_repository')
                ->getParticipantTemplate($typeView->id)
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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $sheetSpecification = new CanAccess($this->getUser());

        if (!$sheetSpecification->isSatisfiedBy($sheet)) {
            throw new AccessDeniedException('No participant for this user attached on this sheet');
        }

        $typeView = $this
            ->get('vimeet_infrastructure.repository.type_repository')
            ->getTypeViewById($sheet->getType()->getId(), $request->getLocale());

        $participantViews = $this
            ->get('vimeet_infrastructure.repository.participant_repository')
            ->getParticipantViewsBySheet($sheet->getId());

        return $this->render('VimeetAppBundle:Event:sheet.html.twig', [
            'eventView'        => $eventView,
            'typeView'         => $typeView,
            'sheet'            => $sheet,
            'participantViews' => $participantViews,
        ]);
    }

    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     *
     * @return RedirectResponse|Response
     */
    public function addParticipantAction(Request $request, EventView $eventView, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $add  = new Add($sheet, $request->getLocale());
        $form = $this->createForm(new AddParticipantType(), $add, [
            'template' => $sheet->getType()->getParticipantTemplate(),
            'locale'   => $request->getLocale(),
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('vimeet_infrastructure.vimeet.application.command.participant.add_handler')->handle($add);
                $this->addFlash('success', 'flash.event.sheet.add_participant.success');

                // Go to the sheet
                return $this->redirectToRoute('event_sheet', [
                    'subdomain' => $request->attributes->get('subdomain'),
                    'id'        => $sheet->getId(),
                ]);
            } catch (ParticipantAlreadyExistException $exception) {
                $error = new FormError($this->get('translator')->trans('event.sheet.participant.already_exists'));
                $form->get('email')->addError($error);
            }
        }

        return $this->render('VimeetAppBundle:Event:addParticipant.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @ParamConverter(
     *   "participant",
     *   class="Proximum\Vimeet\Domain\Model\Participant",
     *   options={"id" = "participant_id"}
     * )
     *
     * @param Request     $request
     * @param EventView   $eventView
     * @param Sheet       $sheet
     * @param Participant $participant
     *
     * @return Response
     */
    public function updateParticipantAction(
        Request $request,
        EventView $eventView,
        Sheet $sheet,
        Participant $participant
    ) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if ($this->getUser()->getId() !== $participant->getUser()->getId()) {
            throw new AccessDeniedException('You can not update other participant');
        }

        $updateParticipant = new Update($participant->getId(), $participant->getData());
        $form              = $this->createForm(new ParticipantUpdateType(), $updateParticipant, [
            'template' => $sheet->getType()->getParticipantTemplate(),
            'locale'   => $request->getLocale(),
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this
                ->get('vimeet_infrastructure.vimeet.application.command.participant.update_handler')
                ->handle($updateParticipant);

            $this->addFlash('success', 'flash.sheet.update_participant.success');

            // Go to the sheet
            return $this->redirectToRoute('event_sheet', [
                'subdomain' => $request->attributes->get('subdomain'),
                'id'        => $sheet->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Event:updateParticipant.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param Request   $request
     * @param EventView $eventView
     * @param Sheet     $sheet
     * @param integer   $block
     *
     * @return Response
     */
    public function updateBlockAction(Request $request, EventView $eventView, Sheet $sheet, $block)
    {
        $updateBlock = new UpdateBlock($sheet, $block);
        $form        = $this->createForm(new UpdateBlockType(), $updateBlock, [
            'template' => $sheet->getType()->getSheetTemplate()[$block]['template'],
            'locale' => $request->getLocale(),
        ]);
        $form->add('submit', 'submit');

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this
                ->get('vimeet_infrastructure.vimeet.application.command.sheet.update_block_handler')
                ->handle($updateBlock);

            $this->addFlash('success', 'flash.event.sheet.update_block.success');

            // Go to the sheet
            return $this->redirectToRoute('event_sheet', [
                'subdomain' => $request->attributes->get('subdomain'),
                'id'        => $sheet->getId(),
            ]);
        }

        return $this->render('VimeetAppBundle:Event:updateBlock.html.twig', [
            'eventView' => $eventView,
            'sheet'     => $sheet,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param Request     $request
     * @param Sheet       $sheet
     * @param Participant $participant
     *
     * @return RedirectResponse
     */
    public function deleteParticipantAction(Request $request, Sheet $sheet, Participant $participant)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $delete = new Delete($sheet, $this->getUser(), $participant);

        try {
            $this->get('vimeet_infrastructure.vimeet.application.command.participant.delete_handler')->handle($delete);
            $this->addFlash('success', 'flash.event.sheet.delete_participant.success');
        } catch (AccessDeniedException $exception) {
            $this->addFlash('error', 'flash.event.sheet.delete_participant.access_denied.error');
        } catch (OwnerCanNotBeDeletedException $exception) {
            $this->addFlash('error', 'flash.event.sheet.delete_participant.access_denied.error');
        } catch (isNotOwnerException $exception) {
            $this->addFlash('error', 'flash.event.sheet.delete_participant.access_denied.error');
        }

        // Go to the sheet
        return $this->redirectToRoute('event_sheet', [
            'subdomain' => $request->attributes->get('subdomain'),
            'id'        => $sheet->getId(),
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
}
