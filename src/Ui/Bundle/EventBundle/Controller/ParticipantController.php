<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Participant\UpdateAvatar;
use Proximum\Vimeet\Application\Command\Participant\UpdateCompany;
use Proximum\Vimeet\Application\Command\Participant\UpdateProfile;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Participant\CardViewQuery;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\AvatarType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\CompanyType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\ProfileType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Profile\PreUpdate;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Profile\PreUpdateHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ParticipantController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param Participant $participant
     *
     * @return Response
     */
    public function seeAction(Request $request, EventDomain $eventDomain, Sheet $sheet, Participant $participant)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $locale             = $request->getLocale();
        $user               = $this->getUser();
        $participantManager = $this->get('components.participant.participant_manager');

        if (!$participantManager->isUserAllowedToEditParticipant($sheet, $participant, $user)) {
            throw $this->createAccessDeniedException('You are not allowed to update this participant');
        }

        $template        = $this->get('template.template_data_factory')->createRegistrationFromParticipant($participant, $locale);
        $profileTemplate = $template->getProfileObjects();
        $avatarTemplate  = $template->getAvatarObjects();
        $companyTemplate = $template->getEditableSheetDataExceptedImageObjects();

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
            'event'           => $eventDomain->getEvent(),
            'participant'     => $participant,
            'profileTemplate' => $profileTemplate,
            'sheet'           => $sheet,
        ]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param Participant $participant
     *
     * @return Response|RedirectResponse
     */
    public function updateProfileAction(Request $request, EventDomain $eventDomain, Sheet $sheet, Participant $participant)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $user               = $this->getUser();
        $locale             = $request->getLocale();
        $participantManager = $this->get('components.participant.participant_manager');

        if (!$participantManager->isUserAllowedToEditParticipant($sheet, $participant, $user)) {
            throw $this->createAccessDeniedException('You are not allowed to update this participant');
        }

        $profileTemplate = $this->get('template.template_data_factory')->createProfileTemplate($participant, $locale);

        if ($participant->getUser() === $user) {
            $profileTemplate = $this->get('account.synchronizer')->get($profileTemplate, $user);
        }

        $form = $this->createForm(ProfileType::class, $profileTemplate, [
            'locale'   => $locale,
            'locales'  => $eventDomain->getEvent()->getLocales(),
            'template' => $profileTemplate,
            'country'  => $eventDomain->getEvent()->getCountry(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $data = array_filter($profileTemplate->getData(), function ($value) {
                return null !== $value;
            });

            $preUpdateView = $this->get('handler_user_profile.pre_update_handler')->handle(
                new PreUpdate($user, $participant, $eventDomain->getEvent(), $data, $profileTemplate, $locale)
            );

            $updateProfile = new UpdateProfile($profileTemplate, $participant, $locale, $data, $user);
            $this->get('tactician.commandbus')->handle($updateProfile);

            if (PreUpdateHandler::MOBILE_VALIDATION_NEEDED === $preUpdateView->preUpdateState) {
                return $this->redirectToRoute('event_user_phone_validate', [
                    'sheet'       => $sheet->getId(),
                    'participant' => $participant->getId(),
                    'mobile'      => $preUpdateView->currentMobile,
                ]);
            }

            return $this->redirectToRoute('event_account_participant', [
                'participant' => $participant->getId(),
                'sheet'       => $sheet->getId(),
            ]);
        }

        return $this->render('EventBundle:Participant:updateProfile.html.twig', [
            'event' => $eventDomain->getEvent(),
            'sheet' => $sheet,
            'participant' => $participant,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param Participant $participant
     * @param string      $key
     *
     * @return Response|RedirectResponse
     */
    public function updateAvatarAction(Request $request, EventDomain $eventDomain, Sheet $sheet, Participant $participant, $key)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $user               = $this->getUser();
        $locale             = $request->getLocale();
        $participantManager = $this->get('components.participant.participant_manager');

        if (!$participantManager->isUserAllowedToEditParticipant($sheet, $participant, $user)) {
            throw $this->createAccessDeniedException('You are not allowed to update this participant');
        }

        $profileTemplate = $this->get('template.template_data_factory')->createProfileTemplate($participant, $locale);
        try {
            $image = $profileTemplate->getObject($key);

            if (!$image instanceof Template\TemplateObject\Image || !$image->hasTag(Tag::PARTICIPANT_AVATAR)) {
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
            $file = $form->get($key)->get('file')->getData();

            try {
                $imagePath   = $image->getImage();
                $fileStorage = $this->get('adapter.local_file_storage');
                $newImage    = $fileStorage->upload($file);
                $image->setImage($newImage);

                $updateAvatar = new UpdateAvatar($profileTemplate, $participant, $locale, $user);
                $this->get('tactician.commandbus')->handle($updateAvatar);

                if (null !== $imagePath) {
                    $fileStorage->remove($imagePath, true);
                }

                return $this->redirectToRoute('event_account_participant', [
                    'participant' => $participant->getId(),
                    'sheet'       => $sheet->getId(),
                ]);
            } catch (\Exception $exception) {
                $form->addError(new FormError('account.profile.updateAvatar.error'));
            }
        }

        return $this->render('EventBundle:Participant:updateAvatar.html.twig', [
            'event' => $eventDomain->getEvent(),
            'sheet' => $sheet,
            'card'  => $card,
            'form'  => $form->createView(),
            'key'   => $key,
        ]);
    }

    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     *
     * @return Response|RedirectResponse
     */
    public function updateCompanyAction(Request $request, EventDomain $eventDomain, Sheet $sheet)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $user   = $this->getUser();
        $locale = $request->getLocale();

        $participant = $sheet->getUserParticipant($user);
        $template    = $this->get('template.template_data_factory')->createCompanyTemplate($sheet, $locale);

        if (empty($template->getEditableSheetDataExceptedImageObjects())) {
            throw $this->createNotFoundException('No company object in this template');
        }

        $form = $this->createForm(CompanyType::class, $template, [
            'locale'   => $locale,
            'locales'  => $eventDomain->getEvent()->getLocales(),
            'template' => $template,
            'country'  => $eventDomain->getEvent()->getCountry(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $data = array_filter($template->getData(), function ($value) {
                return null !== $value;
            });

            $updateProfile = new UpdateCompany($template, $sheet, $participant, $locale, $data, $user);
            $this->get('tactician.commandbus')->handle($updateProfile);

            return $this->redirectToRoute('event_sheet_default', ['sheet' => $sheet->getId()]);
        }

        return $this->render('EventBundle:Participant:updateCompany.html.twig', [
            'event' => $eventDomain->getEvent(),
            'sheet' => $sheet,
            'form'  => $form->createView(),
        ]);
    }
}
