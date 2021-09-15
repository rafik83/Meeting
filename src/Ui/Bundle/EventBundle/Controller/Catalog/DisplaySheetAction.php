<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Controller\Catalog;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Command\Sheet\SheetViewed\Add as AddSheetViewed;
use Proximum\Vimeet\Application\Components\Rule\ParticipantInfoAccessRulesResolver;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetViewedEvent;
use Proximum\Vimeet\Domain\Exception\Sheet\AccessDeniedException;
use Proximum\Vimeet\Domain\KeyDates\Checker\AnsweringMeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingRequestAccessChecker;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\Rule\Applyer;
use Proximum\Vimeet\Domain\Sheet\CanSeeSheet;
use Proximum\Vimeet\Domain\Sheet\SheetInfoGetter;
use Proximum\Vimeet\Domain\Template\TaggedDataFactory;
use Proximum\Vimeet\Domain\User\Phone\ValidationRequiredChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter\EventDomain;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Security\SheetVoter;
use Proximum\Vimeet\Ui\Bundle\EventBundle\ValueResolver\UserDomain;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Twig\Environment;

/**
 * Display a sheet.
 */
class DisplaySheetAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationCheckerAdapter;

    /** @var Environment */
    private $twig;

    /** @var RouterInterface */
    private $router;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var RuleRepositoryInterface */
    private $ruleRepository;

    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var CanSeeSheet */
    private $canSeeSheet;

    /** @var SheetInfoGetter */
    private $sheetInfoGetter;

    /** @var TaggedDataFactory */
    private $taggedDataFactory;

    /** @var MeetingPublishedAccessChecker */
    private $meetingPublishedAccessChecker;

    /** @var MeetingRequestAccessChecker */
    private $meetingRequestAccessChecker;

    /** @var AnsweringMeetingRequestAccessChecker */
    private $answeringMeetingRequestAccessChecker;

    private ParticipantInfoAccessRulesResolver $participantInfoAccessRulesResolver;

    /** @var ValidationRequiredChecker */
    private $validationRequiredChecker;

    /** @var DelayedEventDispatcherInterface */
    private $delayedEventDispatcher;

    /** @var Applyer */
    private $applyer;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationCheckerAdapter,
        Environment $twig,
        RouterInterface $router,
        CommandBusInterface $commandBus,
        SheetRepositoryInterface $sheetRepository,
        RuleRepositoryInterface $ruleRepository,
        RequestRepositoryInterface $requestRepository,
        CanSeeSheet $canSeeSheet,
        SheetInfoGetter $sheetInfoGetter,
        TaggedDataFactory $taggedDataFactory,
        MeetingPublishedAccessChecker $meetingPublishedAccessChecker,
        MeetingRequestAccessChecker $meetingRequestAccessChecker,
        AnsweringMeetingRequestAccessChecker $answeringMeetingRequestAccessChecker,
        ParticipantInfoAccessRulesResolver $participantInfoAccessRulesResolver,
        ValidationRequiredChecker $validationRequiredChecker,
        DelayedEventDispatcherInterface $delayedEventDispatcher,
        Applyer $applyer
    ) {
        $this->authorizationCheckerAdapter = $authorizationCheckerAdapter;
        $this->twig = $twig;
        $this->router = $router;
        $this->commandBus = $commandBus;
        $this->sheetRepository = $sheetRepository;
        $this->ruleRepository = $ruleRepository;
        $this->requestRepository = $requestRepository;
        $this->canSeeSheet = $canSeeSheet;
        $this->sheetInfoGetter = $sheetInfoGetter;
        $this->taggedDataFactory = $taggedDataFactory;
        $this->meetingPublishedAccessChecker = $meetingPublishedAccessChecker;
        $this->meetingRequestAccessChecker = $meetingRequestAccessChecker;
        $this->answeringMeetingRequestAccessChecker = $answeringMeetingRequestAccessChecker;
        $this->participantInfoAccessRulesResolver = $participantInfoAccessRulesResolver;
        $this->validationRequiredChecker = $validationRequiredChecker;
        $this->delayedEventDispatcher = $delayedEventDispatcher;
        $this->applyer = $applyer;
    }

    public function __invoke(
        Request $request,
        EventDomain $eventDomain,
        Sheet $sheet,
        int $sheetToDisplay,
        UserDomain $userDomain
    ): Response {
        if (
            !$this->authorizationCheckerAdapter->isGranted('IS_AUTHENTICATED_REMEMBERED')
            || !$this->authorizationCheckerAdapter->isGranted(SheetVoter::EDIT, $sheet)
        ) {
            throw new AccessDeniedHttpException('Access denied to this sheet');
        }

        $user = $userDomain->getUser();
        $event = $eventDomain->getEvent();
        $locale = $request->getLocale();

        if ($event !== $sheet->getEvent()) {
            throw new NotFoundHttpException('Sheet not in this event');
        }

        if (!$sheet->isInInternalCatalog()) {
            throw new NotFoundHttpException('Sheet not in catalog');
        }

        $seingSheet = $this->sheetRepository->getSheetById($sheetToDisplay);

        if (null === $seingSheet || $event !== $seingSheet->getEvent()) {
            throw new NotFoundHttpException('Sheet not found');
        }

        if (!$seingSheet->isInInternalCatalog()) {
            throw new NotFoundHttpException('Sheet to display not in catalog');
        }

        if (false === $this->canSeeSheet->isSatisfiedBy($sheet, $seingSheet)) {
            throw new NotFoundHttpException('You do not have the right to see this sheet');
        }

        $rules = $this->ruleRepository->getBySeerSheetAndSeeableSheet($sheet, $seingSheet);

        // legacy analytics
        $this->commandBus->handle(new AddSheetViewed($user, $seingSheet));
        // analytics
        $this->delayedEventDispatcher->dispatch(Events::SHEET_VIEWED, new SheetViewedEvent($seingSheet, $user));

        try {
            [$nomenclatures, $participants, $taggedData] = $this
                ->sheetInfoGetter
                ->sheetInfos(
                    $eventDomain->getEvent(),
                    $sheet,
                    $seingSheet,
                    $user,
                    $locale
                );
        } catch (AccessDeniedException $exception) {
            throw new AccessDeniedHttpException();
        }

        // Build sheet template data and attach tagged data view to template object with tags
        $templateData = $this->taggedDataFactory
            ->buildTaggedDataView($seingSheet, $locale, $rules);

        $this->applyer->applyRuleForTemplate($templateData, $rules);
        $this->applyer->applyRuleForCardList($participants, $rules);

        $isMeetingPublished = false;
        $isMeetingRequestUpdateLocked = false;
        $isMeetingRequestClosed = false;
        $isAnsweringMeetingRequestClosed = false;
        $canRequestMeeting = false;

        if ($sheet === $seingSheet) {
            $meetingRequest = null;
        } else {
            $meetingRequest = $this->requestRepository->getRequestBetweenSheets($seingSheet, $sheet);

            $isMeetingPublished = $this->meetingPublishedAccessChecker->allowedToAccess($event);

            $isMeetingRequestUpdateLocked = $event->getConfiguration()->isMeetingRequestUpdateLocked();
            $isMeetingRequestClosed = !$this->meetingRequestAccessChecker->allowedToAccess($event);
            $isAnsweringMeetingRequestClosed = !$this->answeringMeetingRequestAccessChecker->allowedToAccess($event);
            $participantInfoAccessRule = $this->participantInfoAccessRulesResolver->getParticipantInfoAccessRule($sheet, $seingSheet);
            $canRequestMeeting = $participantInfoAccessRule->canRequestMeeting();
        }

        $isPhoneValidationRequired = $this->validationRequiredChecker->handle($sheet, $user, $locale);

        if (true === $isPhoneValidationRequired) {
            $participant = $sheet->getUserParticipant($user);

            if (null !== $participant) {
                $phoneValidationLink = $this->router->generate('event_user_phone_redirect_to_validation', [
                    'sheet' => $sheet->getId(),
                    'participant' => $participant->getId(),
                    'redirectTo' => $this->router->generate('event_catalog_complete_sheet', [
                        'sheet' => $sheet->getId(),
                        'sheetToDisplay' => $seingSheet->getId(),
                    ]),
                ]);
            }
        }

        return new Response($this->twig->render('@Event/Catalog/displaySheet.html.twig', [
            'event' => $event,
            'sheet' => $sheet,
            'participant' => $sheet->getUserParticipant($user),
            'sheetToDisplay' => $seingSheet,
            'taggedData' => $taggedData,
            'locale' => $locale,
            'nomenclatures' => $nomenclatures,
            'participants' => $participants,
            'templateData' => $templateData,
            'meetingRequest' => $meetingRequest,
            'isMeetingPublished' => $isMeetingPublished,
            'isMeetingRequestUpdateLocked' => $isMeetingRequestUpdateLocked,
            'isMeetingRequestClosed' => $isMeetingRequestClosed,
            'isAnsweringMeetingRequestClosed' => $isAnsweringMeetingRequestClosed,
            'isPhoneValidationRequired' => $isPhoneValidationRequired,
            'phoneValidationLink' => $phoneValidationLink ?? null,
            'isRequestMeetingEnabled' => $sheet !== $seingSheet,
            'isCatalog' => true,
            'hideMeetingRequest' => !$canRequestMeeting,
        ]));
    }
}
