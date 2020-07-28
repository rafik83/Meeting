<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Command\Sheet\RemoveImage;
use Proximum\Vimeet\Application\Command\Sheet\SubmitValidation;
use Proximum\Vimeet\Application\Command\Sheet\UpdateData;
use Proximum\Vimeet\Application\Command\Sheet\Upload\MultiUploadCollection;
use Proximum\Vimeet\Application\Command\Sheet\Upload\MultiUploadCollectionHandler;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\Query\Sheet\SheetValidationViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\TemplateObjectViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\WelcomeViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Package\IsValidatedRequiredPackageMissing;
use Proximum\Vimeet\Domain\Sheet\CanSeeSheet;
use Proximum\Vimeet\Domain\Sheet\Participant\AddParticipantChecker;
use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Domain\Transaction\IsValidatedTransactionMissing;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Sheet\CreateObjectForm;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Sheet\CreateObjectFormHandler;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

class SheetController extends Controller
{
    /**
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param null|string $locale
     *
     * @return RedirectResponse
     */
    public function redirectToSheetAction(Request $request, EventDomain $eventDomain, $locale = null)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        try {
            $sheet = $this->getUserSheet($eventDomain->getEvent(), $locale ?: $request->getLocale());
        } catch (SheetNotFoundException $sheetNotFoundException) {
            throw $this->createAccessDeniedException('User not have Sheet');
        }

        if (null === $locale) {
            return $this->redirectToRoute('event_sheet_default', ['sheet' => $sheet->getId()]);
        }

        return $this->redirectToRoute('event_sheet_locale', ['sheet' => $sheet->getId(), 'locale' => $locale]);
    }


    /**
     * Display the sheet in the choosen locale (independently from the interface locale).
     *
     * @param Request     $request
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param string      $locale
     *
     * @return RedirectResponse|Response
     */
    public function sheetAction(Request $request, EventDomain $eventDomain, UserDomain $userDomain, Sheet $sheet, $locale = null)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $locale = $locale ?: $request->getLocale();
        $user = $userDomain->getUser();

        $participantRepository = $this->get('vimeet_infrastructure.repository.participant_repository');
        $participant           = $participantRepository->getParticipantForUserAndSheet($user, $sheet);

        if (null !== $participant) {
            $registrationStepManager = $this->get('components.registration.step_manager');
            $redirectStep            = $registrationStepManager->getRedirectStep($sheet, $participant);

            if (true === $redirectStep['redirect']) {
                return $this->redirectToRoute($redirectStep['route'], $redirectStep['parameters']);
            }
        }

        if ($this->get(IsValidatedRequiredPackageMissing::class)->isSatisfiedBy($sheet) ||
            $this->get(IsValidatedTransactionMissing::class)->isSatisfiedBy($sheet)) {
            return $this->redirectToRoute('event_package_redirect_depending_on_context', ['sheet' => $sheet->getId()]);
        }

        if ($sheet->isValidationDraft()) {
            $sheetValidationView = $this->get('tactician.commandbus.query')->handle(
                new SheetValidationViewQuery(
                    $sheet,
                    $locale
                )
            );
        }

        list($nomenclatures, $participants, $taggedData) = $this->get('sheet.infos_helper')->getInfos(
            $sheet,
            $this->getUser(),
            $locale
        );

        // Build sheet template data and attach tagged data view to template object with tags
        $templateData = $this->get('template.tagged_data_factory')->buildTaggedDataView($sheet, $locale);

        $popinWelcome = $this->get('tactician.commandbus.query')->handle(new WelcomeViewQuery($sheet));

        $tipTranslationViewQuery = new TipTranslationViewQuery(
            $sheet,
            $user,
            TipTranslationViewQueryHandler::CONTEXT_SHEET,
            $request->getLocale()
        );
        $tipTranslationViews = $this->get('tactician.commandbus.query')->handle($tipTranslationViewQuery);

        $canAddParticipant = $this->get(AddParticipantChecker::class)->canAddParticipant($sheet);

        return $this->render('EventBundle:Sheet:sheet.html.twig', [
            'canAddParticipant'       => $canAddParticipant,
            'event'                   => $eventDomain->getEvent(),
            'sheet'                   => $sheet,
            'taggedData'              => $taggedData,
            'locale'                  => $locale,
            'nomenclatures'           => $nomenclatures,
            'participants'            => $participants,
            'templateData'            => $templateData,
            'popinWelcome'            => $popinWelcome,
            'sheetValidationView'     => (isset($sheetValidationView)) ? $sheetValidationView : null,
            'isRequestMeetingEnabled' => false,
            'isCatalog'               => false,
            'tipTranslationViews'     => $tipTranslationViews,
            'isPhoneValidationRequired' => false,
        ]);
    }

    /**
     * @param EventDomain   $eventDomain
     * @param Sheet         $sheet
     * @param UserInterface $user
     * @param int           $sheetToDisplay
     * @param string        $locale
     *
     * @return BinaryFileResponse
     */
    public function generatePdfAction(
        EventDomain $eventDomain,
        Sheet $sheet,
        UserInterface $user,
        $sheetToDisplay,
        $locale
    ) {
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $sheetToDisplay = $this
            ->get('vimeet_infrastructure.repository.sheet_repository')
            ->getSheetById($sheetToDisplay);

        if (null === $sheetToDisplay || $eventDomain->getEvent() !== $sheetToDisplay->getEvent()) {
            throw $this->createAccessDeniedException('Sheet not found');
        }

        if ($sheetToDisplay !== $sheet) {
            if (!$sheetToDisplay->isInCatalog()) {
                throw $this->createAccessDeniedException('Sheet not in catalog');
            }

            $canSeeSheet = $this->get(CanSeeSheet::class);

            if (false === $canSeeSheet->isSatisfiedBy($sheet, $sheetToDisplay)) {
                throw $this->createNotFoundException('You do not have the right to see this sheet');
            }
        }

        $pathToPdf = $this->get('printer.sheet_pdf_printer')->generate($user, $sheet, $sheetToDisplay, $locale);

        return new BinaryFileResponse($pathToPdf);
    }

    /**
     * No access restriction, to allow phantomjs to open this route
     *
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param User        $user
     * @param int         $sheetToDisplay
     * @param string      $locale
     *
     * @return Response
     */
    public function printAction(EventDomain $eventDomain, Sheet $sheet, User $user, $sheetToDisplay, $locale)
    {
        $event  = $eventDomain->getEvent();
        $locale = $event->getAvailableLocale($locale);

        $sheetToDisplay = $this
            ->get('vimeet_infrastructure.repository.sheet_repository')
            ->getSheetById($sheetToDisplay);

        if (null === $sheetToDisplay || $event !== $sheetToDisplay->getEvent()) {
            throw $this->createAccessDeniedException('Sheet not found');
        }

        $isCatalogAllowed = $this->get('domain.key_dates.checker.catalog_access_checker')->allowedToAccess($event);

        // Build print template data and attach tagged data view to template object with tags
        $templateData = $this->get('template.tagged_data_factory')->buildTaggedDataViewForPrint(
            $sheetToDisplay,
            $locale
        );

        list($nomenclatures, $participants, $taggedData) = $this->get('sheet.infos_helper')->getInfos(
            $sheetToDisplay,
            $user,
            $locale
        );

        if ($sheetToDisplay !== $sheet) {
            if (!$sheet->isInInternalCatalog() || !$isCatalogAllowed) {
                throw $this->createAccessDeniedException('Sheet not in catalog');
            }

            $canSeeSheet = $this->get(CanSeeSheet::class);

            if (false === $canSeeSheet->isSatisfiedBy($sheet, $sheetToDisplay)) {
                throw $this->createNotFoundException('You do not have the right to see this sheet');
            }

            $rules = $this
                ->get('repository.rule_repository')
                ->getBySeerSheetAndSeeableSheet($sheet, $sheetToDisplay);

            $ruleApplyer = $this->get('domain.rule.applyer');
            $ruleApplyer->applyRuleForTemplate($templateData, $rules);
            $ruleApplyer->applyRuleForCardList($participants, $rules);
        }

        return $this->render('EventBundle:Sheet:print.html.twig', [
            'event'         => $event,
            'userSheet'     => $sheet,
            'sheet'         => $sheetToDisplay,
            'taggedData'    => $taggedData,
            'locale'        => $locale,
            'nomenclatures' => $nomenclatures,
            'participants'  => $participants,
            'templateData'  => $templateData,
        ]);
    }

    /**
     * @param EventDomain $eventDomain
     * @param Sheet       $sheet
     * @param string      $locale
     * @param string      $key
     *
     * @return RedirectResponse
     */
    public function removeImageAction(EventDomain $eventDomain, Sheet $sheet, $locale, $key)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $templateData = $this->get('template.template_data_factory')->createFromSheet($sheet, $locale);
        $object       = $templateData->getObject($key);

        if (!$object instanceof Template\TemplateObject\Image) {
            throw $this->createNotFoundException('The key given is not an image');
        }

        $removeImage = new RemoveImage($object, $sheet, $templateData);
        $this->get('tactician.commandbus')->handle($removeImage);

        return $this->redirectToRoute('event_sheet_default', ['sheet' => $sheet->getId()]);
    }

    /**
     * @param EventDomain   $eventDomain
     * @param Sheet         $sheet
     * @param UserInterface $user
     *
     * @return RedirectResponse
     */
    public function submitValidationAction(EventDomain $eventDomain, Sheet $sheet, UserInterface $user)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $submitValidation = new SubmitValidation($sheet, $user);

        $this->get('tactician.commandbus')->handle($submitValidation);

        return $this->redirectToRoute('event_sheet_default', ['sheet' => $sheet->getId()]);
    }

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return Sheet
     */
    private function getUserSheet(Event $event, $locale)
    {
        return $this->get('sheet.sheet_guesser')->getUserSheet($this->getUser(), $event, $locale);
    }
}
