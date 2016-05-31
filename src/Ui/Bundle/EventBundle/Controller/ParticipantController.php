<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Participant\Delete;
use Proximum\Vimeet\Application\Command\Participant\UpdateAvatar;
use Proximum\Vimeet\Application\Command\Participant\UpdateProfile;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Exception\Participant\DeleteNotAllowedException;
use Proximum\Vimeet\Application\Query\Participant\CardViewQuery;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Domain\View\EventView;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\AvatarType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\ProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ParticipantController extends Controller
{
    /**
     * @param Request     $request
     * @param EventView   $eventView
     * @param Sheet       $sheet
     * @param Participant $participant
     *
     * @return Response
     */
    public function seeAction(Request $request, EventView $eventView, Sheet $sheet, Participant $participant)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $locale             = $request->getLocale();
        $user               = $this->getUser();
        $participantManager = $this->get('components.participant.participant_manager');

        if (!$participantManager->isUserAllowedToEditParticipant($sheet, $participant, $user)) {
            throw $this->createAccessDeniedException('You are not allowed to update this participant');
        }

        $template        = $this->get('template.template_data_factory')->createRegistrationFromParticipant($participant, $locale);
        $profileTemplate = $template->getProfileObjects();
        $avatarTemplate  = $template->getAvatarObjects();
        $companyTemplate = $template->getCompanyObjects();

        $card  = $this->get('tactician.commandbus.query')->handle(
            new CardViewQuery(
                $participant,
                $locale
            )
        );

        return $this->render('EventBundle:Participant:see.html.twig', [
            'avatarTemplate'  => $avatarTemplate,
            'card'            => $card,
            'companyTemplate' => $companyTemplate,
            'eventView'       => $eventView,
            'participant'     => $participant,
            'profileTemplate' => $profileTemplate,
            'sheet'           => $sheet,
        ]);
    }

    /**
     * @param Request     $request
     * @param EventView   $eventView
     * @param Sheet       $sheet
     * @param Participant $participant
     *
     * @return Response|RedirectResponse
     */
    public function updateProfileAction(Request $request, EventView $eventView, Sheet $sheet, Participant $participant)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user               = $this->getUser();
        $locale             = $request->getLocale();
        $participantManager = $this->get('components.participant.participant_manager');

        if (!$participantManager->isUserAllowedToEditParticipant($sheet, $participant, $user)) {
            throw $this->createAccessDeniedException('You are not allowed to update this participant');
        }

        $profileTemplate = $this->get('template.template_data_factory')->createProfileTemplate($participant, $locale);

        $form = $this->createForm(ProfileType::class, $profileTemplate, [
            'locale'   => $locale,
            'template' => $profileTemplate,
            'country'  => $eventView->country,
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $data = array_filter($profileTemplate->getData(), function ($value) {
                return null !== $value;
            });

            $updateProfile = new UpdateProfile($profileTemplate, $participant, $locale, $data, $user);
            $this->get('tactician.commandbus')->handle($updateProfile);

            return $this->redirectToRoute('event_account_participant', [
                'participant' => $participant->getId(),
                'sheet'       => $sheet->getId(),
            ]);
        }

        return $this->render('EventBundle:Participant:updateProfile.html.twig', [
            'eventView' => $eventView,
            'form'      => $form->createView()
        ]);
    }

    /**
     * @param Request     $request
     * @param EventView   $eventView
     * @param Sheet       $sheet
     * @param Participant $participant
     * @param string      $key
     *
     * @return Response|RedirectResponse
     */
    public function updateAvatarAction(Request $request, EventView $eventView, Sheet $sheet, Participant $participant, $key)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user               = $this->getUser();
        $locale             = $request->getLocale();
        $participantManager = $this->get('components.participant.participant_manager');

        if (!$participantManager->isUserAllowedToEditParticipant($sheet, $participant, $user)) {
            throw $this->createAccessDeniedException('You are not allowed to update this participant');
        }

        $profileTemplate = $this->get('template.template_data_factory')->createProfileTemplate($participant, $locale);
        try {
            $image = $profileTemplate->getObject($key);

            if (!$image instanceof Template\Object\Image || !$image->hasTag(Tag::PARTICIPANT_AVATAR)) {
                throw $this->createNotFoundException(sprintf('Key %s of object given is not an image', $key));
            }
        } catch (\Exception $exception) {
            throw $this->createNotFoundException(sprintf('Key %s of avatar unknown', $key));
        }

        $form = $this->createForm(AvatarType::class, $profileTemplate, [
            'template' => $profileTemplate,
            'key'      => $key,
            'locale'   => $locale,
        ]);

        $card  = $this->get('tactician.commandbus.query')->handle(
            new CardViewQuery(
                $participant,
                $locale
            )
        );

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            if ($image instanceof Template\Object\Image) {
                $file = $form->get($key)->get('file')->getData();

                if ($file instanceof UploadedFile) {
                    $imagePath    = $image->getImage();
                    $fileStorage  = $this->get('adapter.local_file_storage');

                    if (null !== $imagePath) {
                        $fileStorage->remove($imagePath);
                    }

                    $newImage = $fileStorage->upload($file);
                    $image->setImage($newImage);
                }
            }

            $updateAvatar = new UpdateAvatar($profileTemplate, $participant, $locale, $user);
            $this->get('tactician.commandbus')->handle($updateAvatar);

            return $this->redirectToRoute('event_account_participant', [
                'participant' => $participant->getId(),
                'sheet'       => $sheet->getId(),
            ]);
        }

        return $this->render('EventBundle:Participant:updateAvatar.html.twig', [
            'eventView' => $eventView,
            'card'      => $card,
            'form'      => $form->createView(),
            'key'       => $key,
        ]);
    }

    /**
     * @param Sheet       $sheet
     * @param Participant $participant
     *
     * @return RedirectResponse
     */
    public function deleteAction(Sheet $sheet, Participant $participant)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!$this->get('components.participant.participant_manager')->isUserAllowedToDeleteParticipant($sheet, $participant, $this->getUser())) {
            throw $this->createAccessDeniedException('You are not allowed to delete this participant');
        }

        $delete = new Delete($sheet, $this->getUser(), $participant);

        try {
            $this->get('tactician.commandbus')->handle($delete);
            $this->addFlash('success', 'flash.sheet.delete_participant.success');
        } catch (DeleteNotAllowedException $exception) {
            $this->addFlash('error', 'flash.sheet.delete_participant.access_denied');
        }

        // Go to the sheet
        return $this->redirectToRoute('event_sheet', ['sheet' => $sheet->getId()]);
    }
}
