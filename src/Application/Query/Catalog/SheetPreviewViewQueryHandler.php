<?php

namespace Proximum\Vimeet\Application\Query\Catalog;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Sheet\Preview\Preview;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQuery;
use Proximum\Vimeet\Application\Query\Participant\CardListViewQueryHandler;
use Proximum\Vimeet\Application\View\Sheet\Catalog\CatalogSheetPreviewView;
use Proximum\Vimeet\Domain\KeyDates\Checker\MeetingPublishedAccessChecker;
use Proximum\Vimeet\Domain\KeyDates\Checker\NetworkingAccessChecker;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Rule\Composer;

class SheetPreviewViewQueryHandler
{
    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var Preview */
    private $preview;

    /** @var Composer */
    private $ruleComposer;

    /** @var RuleRepositoryInterface */
    private $ruleRepository;

    /** @var RequestRepositoryInterface */
    private $meetingRequestRepository;

    /** @var MeetingPublishedAccessChecker */
    private $meetingPublishedAccessChecker;

    /** @var RouterInterface */
    private $router;

    private NetworkingAccessChecker $networkingAccessChecker;
    private CardListViewQueryHandler $cardListViewQueryHandler;

    public function __construct(
        SheetInfoGuesser $sheetInfoGuesser,
        Composer $ruleComposer,
        Preview $preview,
        RuleRepositoryInterface $ruleRepository,
        RequestRepositoryInterface $meetingRequestRepository,
        MeetingPublishedAccessChecker $meetingPublishedAccessChecker,
        NetworkingAccessChecker $networkingAccessChecker,
        CardListViewQueryHandler $cardListViewQueryHandler,
        RouterInterface $router
    ) {
        $this->sheetInfoGuesser              = $sheetInfoGuesser;
        $this->ruleComposer                  = $ruleComposer;
        $this->preview                       = $preview;
        $this->ruleRepository                = $ruleRepository;
        $this->meetingRequestRepository      = $meetingRequestRepository;
        $this->meetingPublishedAccessChecker = $meetingPublishedAccessChecker;
        $this->networkingAccessChecker       = $networkingAccessChecker;
        $this->cardListViewQueryHandler      = $cardListViewQueryHandler;
        $this->router                        = $router;
    }

    /**
     * @param SheetPreviewViewQuery $catalogSheetPreviewViewQuery
     *
     * @return CatalogSheetPreviewView
     */
    public function handle(SheetPreviewViewQuery $catalogSheetPreviewViewQuery)
    {
        $viewer = $catalogSheetPreviewViewQuery->viewer;
        $sheet  = $catalogSheetPreviewViewQuery->sheet;
        $locale = $catalogSheetPreviewViewQuery->locale;
        $rules  = $this->ruleRepository->getBySeerSheetAndSeeableSheet($viewer, $sheet);

        if (!empty($rules)) {
            $rule = $this->ruleComposer->compose($rules);
        } else {
            $rule = null;
        }

        // Get possible meeting request for this sheet
        $meetingRequest   = $this->meetingRequestRepository->getRequestBetweenSheets($viewer, $sheet);
        $meetingPublished = $this->meetingPublishedAccessChecker->allowedToAccess($catalogSheetPreviewViewQuery->event);

        $isMeetingRequestUpdateLocked = $catalogSheetPreviewViewQuery->event->getConfiguration()
            ->isMeetingRequestUpdateLocked();

        $participant = $catalogSheetPreviewViewQuery->viewer
            ->getUserParticipant($catalogSheetPreviewViewQuery->user);

        if (null !== $participant) {
            $validatePhoneLink = $this->router->generate('event_user_phone_redirect_to_validation', [
                'sheet'       => $catalogSheetPreviewViewQuery->viewer->getId(),
                'participant' => $participant->getId(),
                'redirectTo' => $this->router->generate('event_catalog_complete_sheet', [
                    'sheet' => $viewer->getId(),
                    'sheetToDisplay' => $sheet->getId(),
                ]),
            ]);
        }

        $participantList = null;
        if ($this->networkingAccessChecker->isSheetAllowedToAccess($viewer)) {
            $cardListViewQuery = new CardListViewQuery(
                $sheet,
                $catalogSheetPreviewViewQuery->user,
                $catalogSheetPreviewViewQuery->locale,
                false,
                true
            );
            $participantList = $this->cardListViewQueryHandler->handle($cardListViewQuery);
        }

        return new CatalogSheetPreviewView(
            $sheet->getId(),
            $sheet,
            $this->sheetInfoGuesser->guessSheetTitle($sheet, $locale),
            $this->getTypeOrCategoryTitle($catalogSheetPreviewViewQuery->showCategory, $sheet, $locale),
            $this->preview->getPreview($sheet, $locale, $rule),
            $meetingRequest,
            $viewer === $sheet,
            $meetingPublished,
            $isMeetingRequestUpdateLocked,
            $catalogSheetPreviewViewQuery->isMeetingRequestClosed,
            $catalogSheetPreviewViewQuery->isAnsweringMeetingRequestClosed,
            $meetingRequest && $meetingRequest->hasMessage(),
            $catalogSheetPreviewViewQuery->isSeenByCurrentUser,
            $catalogSheetPreviewViewQuery->isMobileValidationRequired,
            $validatePhoneLink ?? null,
            $catalogSheetPreviewViewQuery->isPriority,
            $catalogSheetPreviewViewQuery->canRequestMeeting,
            $participantList
        );
    }

    private function getTypeOrCategoryTitle(bool $showCategory, Sheet $sheet, string $locale): string
    {
        if (false === $showCategory) {
            return $sheet->getType()->getTitle($locale);
        }

        return implode(', ', $sheet->getType()->getCategoriesTitles($locale));
    }
}
