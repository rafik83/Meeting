<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Register\ParticipantStep;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Template\Object\Image;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Model\Email;
use Proximum\Vimeet\Application\Command\Register\RegisterNewUser;
use Proximum\Vimeet\Application\Command\User\Participate;
use Proximum\Vimeet\Application\Exception\User\EmailAlreadyExistsException;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Common\EmailType;
use Proximum\Vimeet\Domain\View\EventView;
use Proximum\Vimeet\Domain\View\TypeView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Register\RegisterNewUserType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\BlockType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
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
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('event');
        }

        $email = new Email();
        $form  = $this->createForm(EmailType::class, $email, [
            'action' => $this->generateUrl('event_register', ['typeView'  => $typeView->id]),
            'method' => 'POST',
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $user = $this->get('vimeet_infrastructure.repository.user_repository')->findByEmail($email->email);

            if (null !== $user) {
                $sheets = $this->get('vimeet_infrastructure.repository.sheet_repository')->getSheetByUserAndEvent($user, $eventView);

                if (!empty($sheets)) {
                    $this->container->get('session')->getFlashBag()->get('login_email');
                    $this->addFlash('login_email', $email->email);
                    $this->addFlash('success', 'flash.event.register.already_known.login');

                    return $this->redirectToRoute('event_login_second_step');
                } else {
                    $this->container->get('session')->getFlashBag()->get('login_email');
                    $this->container->get('session')->getFlashBag()->get('register_type');
                    $this->addFlash('login_email', $email->email);
                    $this->addFlash('register_type', $typeView->id);
                    $this->addFlash('success', 'flash.event.register.already_known.message');

                    return $this->redirectToRoute('event_login_second_step');
                }
            } else {
                // Remove content of register_email bag before setting it
                $this->container->get('session')->getFlashBag()->get('register_email');
                $this->addFlash('register_email', $email->email);

                return $this->redirectToRoute('event_register_new_user', [
                    'typeView' => $typeView->id,
                ]);
            }
        }

        return $this->render('EventBundle:Register:register.html.twig', [
            'form'      => $form->createView(),
            'eventView' => $eventView,
            'typeView'  => $typeView,
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
    public function registerNewUserAction(Request $request, EventView $eventView, TypeView $typeView)
    {
        $registerEmailFlash = $this->container->get('session')->getFlashBag()->get('register_email');

        $email           = array_shift($registerEmailFlash);
        $registerNewUser = new RegisterNewUser($request->getLocale());

        if (null !== $email) {
            $exist = $this->get('vimeet_infrastructure.repository.user_repository')->emailExists($email);

            if ($exist) {
                return $this->redirectToRoute('event_register', [
                    'typeView' => $typeView->id,
                ]);
            }

            $registerNewUser->email = $email;
            $this->addFlash('register_email', $email);
        }

        $form = $this->createForm(RegisterNewUserType::class, $registerNewUser, [
            'action' => $this->generateUrl('event_register_new_user', ['typeView'  => $typeView->id]),
            'method' => 'POST',
        ]);

        if (null === $registerNewUser->email) {
            return $this->redirectToRoute('event_register', [
                'typeView' => $typeView->id,
            ]);
        }

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            try {
                $this->get('tactician.commandbus')->handle($registerNewUser);
                $this->get('adapter.authentication_manager')->authenticate($registerNewUser->user, 'main');

                return $this->redirectToRoute('event_participate', ['typeView'  => $typeView->id]);
            } catch (EmailAlreadyExistsException $exception) {
                $this->container->get('session')->getFlashBag()->get('register_email');

                return $this->redirectToRoute('event_login');
            }
        }

        return $this->render('EventBundle:Register:registerNewUser.html.twig', [
            'email'     => $email,
            'form'      => $form->createView(),
            'eventView' => $eventView,
            'typeView'  => $typeView,
        ]);
    }

    /**
     * Create a participation to an event.
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
        $this->hasUserAlreadyCreatedParticipant($eventView->getId(), $this->getUser()->getId());

        $event = $this->get('vimeet_infrastructure.repository.event_repository')->getById($eventView->id);
        $type  = $this->get('vimeet_infrastructure.repository.type_repository')->getById($typeView->id);

        $locale = $request->getLocale();

        $registrationTemplate = $this->get('template.template_data_factory')
            ->createRegistrationFromType($type, $locale);

        $user = $this->get('vimeet_infrastructure.repository.user_repository')->findByEmail($this->getUser()->getEmail());
        $registrationTemplate = $this->get('account.synchronizer')->get($registrationTemplate, $user);

        $participantBlock = $registrationTemplate->getFirstBlock();

        $form = $this->createForm(BlockType::class, $participantBlock, [
            'event'  => $event,
            'locale' => $locale,
            'block'  => $participantBlock,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $data = array_filter($participantBlock->getData(), function ($value) {
                return null !== $value;
            });

            // Create the participant
            $participate = new Participate($this->getUser(), $event, $type, $locale, $data, $registrationTemplate);
            $this->get('tactician.commandbus')->handle($participate);

            if ($registrationTemplate->getBlocksCount() === 1) {
                // Go to the sheet
                return $this->redirectToRoute('event_sheet');
            } else {
                $nextStep = $registrationTemplate->getNextBlockPosition(1);

                if ($nextStep) {
                    return $this->redirectToRoute('event_participant_step', [
                        'step'        => $nextStep,
                        'participant' => $participate->participant->getId(),
                    ]);
                } else {
                    return $this->redirectToRoute('event_sheet');
                }
            }
        }

        return $this->render('EventBundle:Register:participate.html.twig', [
            'eventView'  => $eventView,
            'typeView'   => $typeView,
            'form'       => $form->createView(),
            'stepTitle'  => $participantBlock->getTitle($locale),
            'stepsCount' => $registrationTemplate->getBlocksCount(),
        ]);
    }

    /**
     * @param Request     $request
     * @param EventView   $eventView
     * @param Participant $participant
     * @param int         $step
     *
     * @return RedirectResponse|Response
     */
    public function participantStepAction(Request $request, EventView $eventView, Participant $participant, $step)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Check if the user has already created a participate
        $participants = $this
            ->get('vimeet_infrastructure.repository.participant_repository')
            ->getParticipantsByUserForEvent($this->getUser()->getId(), $eventView);

        if (0 === count($participants)) {
            throw $this->createAccessDeniedException('Participation does not exist');
        }

        if (!in_array($participant, $participants)) {
            throw $this->createNotFoundException(
                sprintf(
                    'The current user %s is not the owner of this participant %s',
                    $this->getUser()->getId(),
                    $participant->getId()
                )
            );
        }

        $locale = $request->getLocale();

        $registrationTemplate = $this->get('template.template_data_factory')
            ->createRegistrationFromParticipant($participant, $locale);

        $registrationTemplate = $this->get('account.synchronizer')->get($registrationTemplate, $participant->getUser());
        $participantBlock     = $registrationTemplate->getBlock(intval($step));

        if (null === $participantBlock) {
            throw $this->createNotFoundException('Unknown step');
        }

        $event = $this->get('vimeet_infrastructure.repository.event_repository')->getById($eventView->id);

        $form = $this->createForm(BlockType::class, $participantBlock, [
            'event'  => $event,
            'locale' => $locale,
            'block'  => $participantBlock,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $data = array_filter($participantBlock->getData(), function ($value) {
                return null !== $value;
            });

            $data = $this->handleUploadedFiles($registrationTemplate, $form, $data);

            if ($form->isValid()) {
                $participantStep = new ParticipantStep($registrationTemplate, $participant, $step, $locale, $data);
                $this->get('tactician.commandbus')->handle($participantStep);

                if ($registrationTemplate->getBlocksCount() === $step) {
                    // Go to the sheet
                    return $this->redirectToRoute('event_sheet');
                } else {
                    $nextStep = $registrationTemplate->getNextBlockPosition($step);

                    if ($nextStep) {
                        return $this->redirectToRoute('event_participant_step', [
                            'step'        => $nextStep,
                            'participant' => $participant->getId(),
                        ]);
                    } else {
                        return $this->redirectToRoute('event_sheet');
                    }
                }
            }
        }

        $participantInfos = $this->get('template.participant_info_guesser')->guessParticipantInfosWithTemplateData($registrationTemplate, $locale);

        return $this->render('EventBundle:Register:participateStep.html.twig', [
            'eventView'        => $eventView,
            'form'             => $form->createView(),
            'stepsCount'       => $registrationTemplate->getBlocksCount(),
            'stepNumber'       => $step,
            'stepTitle'        => $participantBlock->getTitle($locale),
            'participantInfos' => $participantInfos,
        ]);
    }

    /**
     * @param int $userId
     */
    private function hasUserAlreadyCreatedParticipant($eventId, $userId)
    {
        $participants = $this
            ->get('vimeet_infrastructure.repository.participant_repository')
            ->getAllParticipantForUser($eventId, $userId);

        if (1 <= count($participants)) {
            throw $this->createAccessDeniedException('Participation already created');
        }
    }

    /**
     * @param TemplateData $registrationTemplate
     * @param Form         $form
     * @param array        $data
     * @return array
     */
    private function handleUploadedFiles($registrationTemplate, $form, $data)
    {
        $imageObjects = $registrationTemplate->getImageObjects();
        $fileStorage  = $this->get('adapter.local_file_storage');

        foreach ($imageObjects as $key => $object) {
            if ($form->has($key) && $form->get($key)->getData() !== null) {
                $file = $form->get($key)->getData();

                if ($file instanceof UploadedFile && in_array($file->getClientMimeType(), Image::supportedMimeType())) {
                    if ($form->has($key) && '' !== $object->getContentValue() && $form->get($key)->getData() !== null) {
                        $fileStorage->remove($object->getContentValue());
                    }

                    if ($form->has($key) && $form->get($key)->getData() !== null) {
                        $data[$key]['image'] = $fileStorage->upload($form->get($key)->getData());
                    }
                } else {
                    $form->get($key)->addError(new FormError('validators.field.notValid.image'));
                }
            }
        }

        return $data;
    }
}
