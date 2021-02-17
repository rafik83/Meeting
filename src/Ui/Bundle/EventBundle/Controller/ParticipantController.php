<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Participant\UpdateAvatar;
use Proximum\Vimeet\Application\Command\Participant\UpdateCompany;
use Proximum\Vimeet\Application\Command\Participant\UpdateProfile;
use Proximum\Vimeet\Application\Components\Participant\ParticipantManager;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Query\Participant\CardViewQuery;
use Proximum\Vimeet\Domain\Account\Synchronizer;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\AvatarType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\CompanyType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\ProfileType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Profile\PreUpdate;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\User\Profile\PreUpdateHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ParticipantController extends AbstractController
{
    private ParticipantManager $participantManager;
    private TemplateDataFactory $templateDataFactory;
    private Synchronizer $accountSynchronizer;
    private PreUpdateHandler $userProfilePreUpdateHandler;
    private LocalFileStorageAdapter $fileStorage;
    private QueryBusInterface $queryBus;
    private CommandBusInterface $commandBus;

    public function __construct(
        ParticipantManager $participantManager,
        TemplateDataFactory $templateDataFactory,
        Synchronizer $accountSynchronizer,
        PreUpdateHandler $userProfilePreUpdateHandler,
        LocalFileStorageAdapter $fileStorage,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->participantManager = $participantManager;
        $this->templateDataFactory = $templateDataFactory;
        $this->accountSynchronizer = $accountSynchronizer;
        $this->userProfilePreUpdateHandler = $userProfilePreUpdateHandler;
        $this->fileStorage = $fileStorage;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    public function seeAction(Request $request, EventDomain $eventDomain, Sheet $sheet, Participant $participant): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $locale = $request->getLocale();
        $user = $this->getUser();

        if (!$this->participantManager->isUserAllowedToEditParticipant($sheet, $participant, $user)) {
            throw $this->createAccessDeniedException('You are not allowed to update this participant');
        }

        $template = $this->templateDataFactory->createRegistrationFromParticipant($participant, $locale);
        $profileTemplate = $template->getProfileObjects();
        $avatarTemplate = $template->getAvatarObjects();
        $companyTemplate = $template->getEditableSheetDataExceptedImageObjects();

        $card = $this->queryBus->handle(
            new CardViewQuery(
                $participant,
                $locale
            )
        );

        return $this->render('EventBundle:Participant:see.html.twig', [
            'avatarTemplate' => $avatarTemplate,
            'card' => $card,
            'companyTemplate' => $companyTemplate,
            'event' => $eventDomain->getEvent(),
            'participant' => $participant,
            'profileTemplate' => $profileTemplate,
            'sheet' => $sheet,
        ]);
    }

    public function updateProfileAction(Request $request, EventDomain $eventDomain, Sheet $sheet, Participant $participant): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $user = $this->getUser();
        $locale = $request->getLocale();

        if (!$this->participantManager->isUserAllowedToEditParticipant($sheet, $participant, $user)) {
            throw $this->createAccessDeniedException('You are not allowed to update this participant');
        }

        $profileTemplate = $this->templateDataFactory->createProfileTemplate($participant, $locale);

        if ($participant->getUser() === $user) {
            $profileTemplate = $this->accountSynchronizer->get($profileTemplate, $user);
        }

        $form = $this->createForm(ProfileType::class, $profileTemplate, [
            'locale' => $locale,
            'locales' => $eventDomain->getEvent()->getLocales(),
            'template' => $profileTemplate,
            'country' => $eventDomain->getEvent()->getCountry(),
        ]);

        $askLocale = count($eventDomain->getEvent()->getLocales()) > 1;
        if ($askLocale) {
            $form->get('locale')->setData($participant->getLocale());
        }

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $data = array_filter($profileTemplate->getData(), function ($value) {
                return null !== $value;
            });

            $locale = $askLocale ? $form->get('locale')->getData() : $participant->getLocale();

            $preUpdateView = $this->userProfilePreUpdateHandler->handle(
                new PreUpdate($user, $participant, $eventDomain->getEvent(), $data, $profileTemplate, $locale)
            );

            $updateProfile = new UpdateProfile($profileTemplate, $participant, $locale, $data, $user);
            $this->commandBus->handle($updateProfile);

            if (PreUpdateHandler::MOBILE_VALIDATION_NEEDED === $preUpdateView->preUpdateState) {
                return $this->redirectToRoute('event_user_phone_validate', [
                    'sheet' => $sheet->getId(),
                    'participant' => $participant->getId(),
                    'mobile' => $preUpdateView->currentMobile,
                ]);
            }

            return $this->redirectToRoute('event_account_participant', [
                'participant' => $participant->getId(),
                'sheet' => $sheet->getId(),
            ]);
        }

        return $this->render('EventBundle:Participant:updateProfile.html.twig', [
            'event' => $eventDomain->getEvent(),
            'sheet' => $sheet,
            'participant' => $participant,
            'form' => $form->createView(),
        ]);
    }

    public function updateAvatarAction(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        Participant $participant,
        string $key
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $user = $this->getUser();
        $locale = $request->getLocale();

        if (!$this->participantManager->isUserAllowedToEditParticipant($sheet, $participant, $user)) {
            throw $this->createAccessDeniedException('You are not allowed to update this participant');
        }

        $profileTemplate = $this->templateDataFactory->createProfileTemplate($participant, $locale);
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
            'key' => $key,
            'locale' => $locale,
        ]);

        $card = $this->queryBus->handle(
            new CardViewQuery(
                $participant,
                $locale
            )
        );

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $file = $form->get($key)->get('file')->getData();

            try {
                $imagePath = $image->getImage();
                $newImage = $this->fileStorage->upload($file);
                $image->setImage($newImage);

                $updateAvatar = new UpdateAvatar($profileTemplate, $participant, $locale, $user);
                $this->commandBus->handle($updateAvatar);

                if (null !== $imagePath) {
                    $this->fileStorage->remove($imagePath, true);
                }

                return $this->redirectToRoute('event_account_participant', [
                    'participant' => $participant->getId(),
                    'sheet' => $sheet->getId(),
                ]);
            } catch (\Exception $exception) {
                $form->addError(new FormError('account.profile.updateAvatar.error'));
            }
        }

        return $this->render('EventBundle:Participant:updateAvatar.html.twig', [
            'event' => $eventDomain->getEvent(),
            'sheet' => $sheet,
            'card' => $card,
            'form' => $form->createView(),
            'key' => $key,
        ]);
    }

    public function updateCompanyAction(Request $request, EventDomain $eventDomain, Sheet $sheet): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $user = $this->getUser();
        $locale = $request->getLocale();

        $participant = $sheet->getUserParticipant($user);
        $template = $this->templateDataFactory->createCompanyTemplate($sheet, $locale);

        if (empty($template->getEditableSheetDataExceptedImageObjects())) {
            throw $this->createNotFoundException('No company object in this template');
        }

        $form = $this->createForm(CompanyType::class, $template, [
            'locale' => $locale,
            'locales' => $eventDomain->getEvent()->getLocales(),
            'template' => $template,
            'country' => $eventDomain->getEvent()->getCountry(),
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $data = array_filter($template->getData(), function ($value) {
                return null !== $value;
            });

            $updateProfile = new UpdateCompany($template, $sheet, $participant, $locale, $data, $user);
            $this->commandBus->handle($updateProfile);

            return $this->redirectToRoute('event_sheet_default', ['sheet' => $sheet->getId()]);
        }

        return $this->render('EventBundle:Participant:updateCompany.html.twig', [
            'event' => $eventDomain->getEvent(),
            'sheet' => $sheet,
            'form' => $form->createView(),
        ]);
    }
}
