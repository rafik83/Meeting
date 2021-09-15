<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\Sheet\RemoveImage;
use Proximum\Vimeet\Application\Command\Sheet\SubmitValidation;
use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfosHelper;
use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Application\Query\Sheet\CanDisplayAnalyticsStat;
use Proximum\Vimeet\Application\Query\Sheet\CanDisplayAnalyticsViewLink;
use Proximum\Vimeet\Application\Query\Sheet\SheetValidationViewQuery;
use Proximum\Vimeet\Application\Query\Sheet\WelcomeViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQuery;
use Proximum\Vimeet\Application\Query\Tip\TipTranslationViewQueryHandler;
use Proximum\Vimeet\Domain\KeyDates\Checker\CatalogAccessChecker;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Rule\Applyer;
use Proximum\Vimeet\Domain\Sheet\CanSeeSheet;
use Proximum\Vimeet\Domain\Sheet\Participant\AddParticipantChecker;
use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Domain\Template\TaggedDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Printer\SheetPdfPrinter;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Sheet\SheetRedirectionMiddleware;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class SheetController extends AbstractController
{
    private TemplateDataFactory $templateDataFactory;
    private TaggedDataFactory $taggedDataFactory;
    private AddParticipantChecker $addParticipantChecker;
    private CanDisplayAnalyticsStat $canDisplayAnalyticsStat;
    private CanDisplayAnalyticsViewLink $canDisplayAnalyticsViewLink;
    private CanSeeSheet $canSeeSheet;
    private SheetPdfPrinter $sheetPdfPrinter;
    private SheetInfosHelper $sheetInfosHelper;
    private SheetRepositoryInterface $sheetRepository;
    private SheetGuesser $sheetGuesser;
    private CatalogAccessChecker $catalogAccessChecker;
    private RuleRepositoryInterface $ruleRepository;
    private Applyer $ruleApplyer;
    private SheetRedirectionMiddleware $sheetRedirectionMiddleware;
    private QueryBusInterface $queryBus;
    private CommandBusInterface $commandBus;

    public function __construct(
        TemplateDataFactory $templateDataFactory,
        TaggedDataFactory $taggedDataFactory,
        AddParticipantChecker $addParticipantChecker,
        CanDisplayAnalyticsStat $canDisplayAnalyticsStat,
        CanDisplayAnalyticsViewLink $canDisplayAnalyticsViewLink,
        CanSeeSheet $canSeeSheet,
        SheetPdfPrinter $sheetPdfPrinter,
        SheetInfosHelper $sheetInfosHelper,
        SheetRepositoryInterface $sheetRepository,
        SheetGuesser $sheetGuesser,
        CatalogAccessChecker $catalogAccessChecker,
        RuleRepositoryInterface $ruleRepository,
        Applyer $ruleApplyer,
        SheetRedirectionMiddleware $sheetRedirectionMiddleware,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->templateDataFactory = $templateDataFactory;
        $this->taggedDataFactory = $taggedDataFactory;
        $this->addParticipantChecker = $addParticipantChecker;
        $this->canDisplayAnalyticsStat = $canDisplayAnalyticsStat;
        $this->canDisplayAnalyticsViewLink = $canDisplayAnalyticsViewLink;
        $this->canSeeSheet = $canSeeSheet;
        $this->sheetPdfPrinter = $sheetPdfPrinter;
        $this->sheetInfosHelper = $sheetInfosHelper;
        $this->sheetRepository = $sheetRepository;
        $this->sheetGuesser = $sheetGuesser;
        $this->catalogAccessChecker = $catalogAccessChecker;
        $this->ruleRepository = $ruleRepository;
        $this->ruleApplyer = $ruleApplyer;
        $this->sheetRedirectionMiddleware = $sheetRedirectionMiddleware;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    public function redirectToSheetAction(Request $request, EventDomain $eventDomain, ?string $locale = null): RedirectResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        try {
            $sheet = $this->getUserSheet($eventDomain->getEvent(), $locale ?: $request->getLocale());
        } catch (SheetNotFoundException $sheetNotFoundException) {
            throw new AccessDeniedHttpException('User not have Sheet');
        }

        if (null === $locale) {
            return $this->redirectToRoute('event_sheet_default', ['sheet' => $sheet->getId()]);
        }

        return $this->redirectToRoute('event_sheet_locale', ['sheet' => $sheet->getId(), 'locale' => $locale]);
    }


    /**
     * Display the sheet in the choosen locale (independently from the interface locale).
     */
    public function sheetAction(Request $request, EventDomain $eventDomain, UserDomain $userDomain, Sheet $sheet, ?string $locale = null): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $locale = $locale ?: $request->getLocale();
        $user = $userDomain->getUser();

        $redirectResponse = $this->sheetRedirectionMiddleware->getForceRedirection($sheet, $user);

        if (null !== $redirectResponse) {
            return $redirectResponse;
        }

        if ($sheet->isValidationDraft() && $sheet->getType()->canSubmitValidation()) {
            $sheetValidationView = $this->queryBus->handle(
                new SheetValidationViewQuery(
                    $sheet,
                    $locale
                )
            );
        }

        [$nomenclatures, $participants, $taggedData] = $this->sheetInfosHelper->getInfos(
            $sheet,
            $this->getUser(),
            $locale
        );

        // Build sheet template data and attach tagged data view to template object with tags
        $templateData = $this->taggedDataFactory->buildTaggedDataView($sheet, $locale);

        $popinWelcome = $this->queryBus->handle(new WelcomeViewQuery($sheet));

        $tipTranslationViewQuery = new TipTranslationViewQuery(
            $sheet,
            $user,
            TipTranslationViewQueryHandler::CONTEXT_SHEET,
            $request->getLocale()
        );
        $tipTranslationViews = $this->queryBus->handle($tipTranslationViewQuery);

        $canAddParticipant = $this->addParticipantChecker->canAddParticipant($sheet);

        $displayAnalyticsStat = $this->canDisplayAnalyticsStat->isSatisfiedBy($sheet);

        $displayAnalyticsViewLink = $this->canDisplayAnalyticsViewLink->isSatisfiedBy($sheet);

        return $this->render('EventBundle:Sheet:sheet.html.twig', [
            'canAddParticipant' => $canAddParticipant,
            'event' => $eventDomain->getEvent(),
            'sheet' => $sheet,
            'taggedData' => $taggedData,
            'locale' => $locale,
            'nomenclatures' => $nomenclatures,
            'participants' => $participants,
            'templateData' => $templateData,
            'popinWelcome' => $popinWelcome,
            'sheetValidationView' => $sheetValidationView ?? null,
            'isRequestMeetingEnabled' => false,
            'isCatalog' => false,
            'tipTranslationViews' => $tipTranslationViews,
            'isPhoneValidationRequired' => false,
            'displayAnalyticsStat' => $displayAnalyticsStat,
            'displayAnalyticsViewLink' => $displayAnalyticsViewLink
        ]);
    }

    public function generatePdfAction(
        EventDomain $eventDomain,
        Sheet $sheet,
        UserInterface $user,
        int $sheetToDisplay,
        string $locale
    ): BinaryFileResponse {
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $sheetToDisplay = $this->sheetRepository->getSheetById($sheetToDisplay);

        if (null === $sheetToDisplay || $eventDomain->getEvent() !== $sheetToDisplay->getEvent()) {
            throw $this->createAccessDeniedException('Sheet not found');
        }

        if ($sheetToDisplay !== $sheet) {
            if (!$sheetToDisplay->isInInternalCatalog()) {
                throw $this->createAccessDeniedException('Sheet not in catalog');
            }

            if (false === $this->canSeeSheet->isSatisfiedBy($sheet, $sheetToDisplay)) {
                throw $this->createNotFoundException('You do not have the right to see this sheet');
            }
        }

        $pathToPdf = $this->sheetPdfPrinter->generate($user, $sheet, $sheetToDisplay, $locale);

        return new BinaryFileResponse($pathToPdf);
    }

    /**
     * No access restriction, to allow phantomjs to open this route
     */
    public function printAction(EventDomain $eventDomain, Sheet $sheet, User $user, int $sheetToDisplayId, string $locale): Response
    {
        $event = $eventDomain->getEvent();
        $locale = $event->getAvailableLocale($locale);

        $sheetToDisplay = $this->sheetRepository->getSheetById($sheetToDisplayId);

        if (null === $sheetToDisplay || $event !== $sheetToDisplay->getEvent()) {
            throw $this->createAccessDeniedException('Sheet not found');
        }

        $isCatalogAllowed = $this->catalogAccessChecker->allowedToAccess($event);

        // Build print template data and attach tagged data view to template object with tags
        $templateData = $this->taggedDataFactory->buildTaggedDataViewForPrint(
            $sheetToDisplay,
            $locale
        );

        [$nomenclatures, $participants, $taggedData] = $this->sheetInfosHelper->getInfos(
            $sheetToDisplay,
            $user,
            $locale
        );

        if ($sheetToDisplay !== $sheet) {
            if (!$sheet->isInInternalCatalog() || !$isCatalogAllowed) {
                throw $this->createAccessDeniedException('Sheet not in catalog');
            }

            if (false === $this->canSeeSheet->isSatisfiedBy($sheet, $sheetToDisplay)) {
                throw $this->createNotFoundException('You do not have the right to see this sheet');
            }

            $rules = $this->ruleRepository->getBySeerSheetAndSeeableSheet($sheet, $sheetToDisplay);

            $this->ruleApplyer->applyRuleForTemplate($templateData, $rules);
            $this->ruleApplyer->applyRuleForCardList($participants, $rules);
        }

        return $this->render('EventBundle:Sheet:print.html.twig', [
            'event' => $event,
            'userSheet' => $sheet,
            'sheet' => $sheetToDisplay,
            'taggedData' => $taggedData,
            'locale' => $locale,
            'nomenclatures' => $nomenclatures,
            'participants' => $participants,
            'templateData' => $templateData,
        ]);
    }

    public function removeImageAction(Sheet $sheet, string $locale, string $key): RedirectResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        $templateData = $this->templateDataFactory->createFromSheet($sheet, $locale);
        $object = $templateData->getObject($key);

        if (!$object instanceof Template\TemplateObject\Image) {
            throw $this->createNotFoundException('The key given is not an image');
        }

        $removeImage = new RemoveImage($object, $sheet, $templateData);
        $this->commandBus->handle($removeImage);

        return $this->redirectToRoute('event_sheet_default', ['sheet' => $sheet->getId()]);
    }

    public function submitValidationAction(Sheet $sheet, UserInterface $user): RedirectResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted(SheetVoter::EDIT, $sheet);

        if (!$sheet->getType()->canSubmitValidation()) {
            throw $this->createAccessDeniedException();
        }

        $submitValidation = new SubmitValidation($sheet, $user);

        $this->commandBus->handle($submitValidation);

        return $this->redirectToRoute('event_sheet_default', ['sheet' => $sheet->getId()]);
    }

    private function getUserSheet(Event $event, string $locale): Sheet
    {
        return $this->sheetGuesser->getUserSheet($this->getUser(), $event, $locale);
    }
}
