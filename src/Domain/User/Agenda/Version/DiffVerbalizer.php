<?php

namespace Proximum\Vimeet\Domain\User\Agenda\Version;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Spot;
use Proximum\Vimeet\Domain\Model\User\Agenda\Version;
use Proximum\Vimeet\Domain\Repository\Meeting\MessageRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SpotRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\View\MeetingAddedView;
use Proximum\Vimeet\Domain\User\Agenda\Version\View\MeetingDeletedView;
use Proximum\Vimeet\Domain\User\Agenda\Version\View\MeetingMovedView;

/**
 * Translate the diff into comprehensible sentence for the user
 */
class DiffVerbalizer
{
    const TRANSLATION_AGENDA_MODIFIED = 'user.agenda.version.agendaModified';
    const TRANSLATION_MEETING_ADDED = 'user.agenda.version.meetingAdded';
    const TRANSLATION_MEETING_DELETED = 'user.agenda.version.meetingDeleted';
    const TRANSLATION_MEETING_MOVED = 'user.agenda.version.meetingMoved';
    const TRANSLATION_MEETING_CHANGED_WITH_MESSAGE = 'user.agenda.version.meetingChangedWithMessage';
    const TRANSLATION_DOMAIN = 'messages';

    /** @var Sheet[] */
    private $sheets;

    /** @var MeetingSlot[] */
    private $slots;

    /** @var Spot[] */
    private $spots;

    /** @var TranslatorInterface */
    private $translator;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var MeetingSlotRepositoryInterface */
    private $meetingSlotRepository;

    /** @var SpotRepositoryInterface */
    private $spotRepository;

    /** @var \IntlDateFormatter */
    private $dayFormatter;

    /** @var \IntlDateFormatter */
    private $hourFormatter;

    /** @var DiffChecker */
    private $diffChecker;

    /** @var MessageRepositoryInterface */
    private $messageRepository;

    /** @var string[] */
    private $messages;

    public function __construct(
        DiffChecker $diffChecker,
        TranslatorInterface $translator,
        SheetRepositoryInterface $sheetRepository,
        MeetingSlotRepositoryInterface $meetingSlotRepository,
        SpotRepositoryInterface $spotRepository,
        MessageRepositoryInterface $messageRepository
    ) {
        $this->diffChecker = $diffChecker;
        $this->translator = $translator;
        $this->sheetRepository = $sheetRepository;
        $this->meetingSlotRepository = $meetingSlotRepository;
        $this->spotRepository = $spotRepository;
        $this->messageRepository = $messageRepository;
    }

    public function verbalizeDiff(Version $lastVersion, array $currentVersion, string $locale): string
    {
        $diffs = [];

        $deletedRequests = array_diff_key($lastVersion->getVersion(), $currentVersion);
        $addedRequests = array_diff_key($currentVersion, $lastVersion->getVersion());
        $toCheckChanges = array_intersect_key($lastVersion->getVersion(), $currentVersion);

        if (empty($deletedRequests) && empty($addedRequests) && empty($toCheckChanges)) {
            return '';
        }

        $userSheets = $this->sheetRepository
            ->getSheetsByUserAndEvent($lastVersion->getUser(), $lastVersion->getEvent());

        $deletedRequestViews = $this->getDeletedMeeting($userSheets, $deletedRequests);
        $addedRequestViews = $this->getAddedMeeting($userSheets, $addedRequests);
        $changedViews = $this->getChangedMeeting($userSheets, $toCheckChanges, $currentVersion);
        $this->getInfo($deletedRequestViews, $addedRequestViews, $changedViews, $userSheets);

        $this->dayFormatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            $lastVersion->getEvent()->getTimeZone()
        );
        $this->hourFormatter = new \IntlDateFormatter(
            $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::SHORT,
            $lastVersion->getEvent()->getTimeZone()
        );

        $this->addDeletedSentences($diffs, $deletedRequestViews, $locale);
        $this->addAddedSentences($diffs, $addedRequestViews, $locale);
        $this->addMovedSentences($diffs, $changedViews, $locale);

        return implode("\n", $diffs);
    }

    /**
     * @param string[]             $diffs
     * @param MeetingDeletedView[] $deletedRequestViews
     * @param string               $locale
     */
    private function addDeletedSentences(array &$diffs, array &$deletedRequestViews, string $locale): void
    {
        foreach ($deletedRequestViews as $deletedRequestView) {
            if (isset($this->sheets[$deletedRequestView->sheetId])) {
                $sentence = $this->translator->trans(
                    self::TRANSLATION_MEETING_DELETED,
                    ['%sheetTitle%' => $this->sheets[$deletedRequestView->sheetId]->getTitle()],
                    self::TRANSLATION_DOMAIN,
                    $locale
                );

                $latestMessage = $this->messages[$deletedRequestView->requestId] ?? null;
                if ($latestMessage !== null) {
                    $sentence .= ' ' . $this->translator->trans(
                            self::TRANSLATION_MEETING_CHANGED_WITH_MESSAGE,
                            ['%message%' => $latestMessage],
                            self::TRANSLATION_DOMAIN,
                            $locale
                        );
                }

                $diffs[] = $sentence;
            }
        }
    }

    /**
     * @param string[]           $diffs
     * @param MeetingAddedView[] $addedRequestViews
     * @param string             $locale
     */
    private function addAddedSentences(array &$diffs, array &$addedRequestViews, string $locale): void
    {
        foreach ($addedRequestViews as $addedRequestView) {
            if (isset($this->sheets[$addedRequestView->sheetId])
                && isset($this->slots[$addedRequestView->slotId])
                && isset($this->spots[$addedRequestView->spotId])
            ) {
                $diffs[] = $this->translator->trans(
                    self::TRANSLATION_MEETING_ADDED,
                    [
                        '%sheetTitle%' => $this->sheets[$addedRequestView->sheetId]->getTitle(),
                        '%day%' => $this->formatDay($this->slots[$addedRequestView->slotId]->getBegin()),
                        '%hour%' => $this->formatHour($this->slots[$addedRequestView->slotId]->getBegin()),
                        '%spotRef%' => $this->spots[$addedRequestView->spotId]->getReference(),
                    ],
                    self::TRANSLATION_DOMAIN,
                    $locale
                );
            }
        }
    }

    /**
     * @param string[]           $diffs
     * @param MeetingMovedView[] $changedMeetings
     * @param string             $locale
     */
    private function addMovedSentences(array &$diffs, array &$changedMeetings, string $locale): void
    {
        foreach ($changedMeetings as $changedMeeting) {
            if (isset($this->sheets[$changedMeeting->sheetId])
                && isset($this->slots[$changedMeeting->slotId])
                && isset($this->spots[$changedMeeting->spotId])
            ) {
                $sentence = $this->translator->trans(
                    self::TRANSLATION_MEETING_MOVED,
                    [
                        '%sheetTitle%' => $this->sheets[$changedMeeting->sheetId]->getTitle(),
                        '%day%' => $this->formatDay($this->slots[$changedMeeting->slotId]->getBegin()),
                        '%hour%' => $this->formatHour($this->slots[$changedMeeting->slotId]->getBegin()),
                        '%spotRef%' => $this->spots[$changedMeeting->spotId]->getReference(),
                    ],
                    self::TRANSLATION_DOMAIN,
                    $locale
                );

                $latestMessage = $this->messages[$changedMeeting->requestId] ?? null;
                if ($latestMessage !== null) {
                    $sentence .= ' ' . $this->translator->trans(
                            self::TRANSLATION_MEETING_CHANGED_WITH_MESSAGE,
                            ['%message%' => $latestMessage],
                            self::TRANSLATION_DOMAIN,
                            $locale
                        );
                }

                $diffs[] = $sentence;
            }
        }
    }

    /**
     * Retrieve the info to avoid doing multiple queries
     *
     * @param MeetingDeletedView[] $deletedRequestViews
     * @param MeetingAddedView[]   $addedRequestViews
     * @param MeetingMovedView[]   $changedViews
     * @param Sheet[]              $userSheets
     */
    private function getInfo(
        array $deletedRequestViews,
        array $addedRequestViews,
        array $changedViews,
        array $userSheets
    ): void {
        $sheets = [];
        $slots = [];
        $spots = [];

        foreach ($deletedRequestViews as $deletedRequestView) {
            $sheets[$deletedRequestView->sheetId] = $deletedRequestView->sheetId;
        }

        foreach ($addedRequestViews as $addedRequestView) {
            $sheets[$addedRequestView->sheetId] = $addedRequestView->sheetId;
            $spots[$addedRequestView->spotId] = $addedRequestView->spotId;
            $slots[$addedRequestView->slotId] = $addedRequestView->slotId;
        }

        foreach ($changedViews as $changedView) {
            $sheets[$changedView->sheetId] = $changedView->sheetId;
            $spots[$changedView->spotId] = $changedView->spotId;
            $slots[$changedView->slotId] = $changedView->slotId;
        }

        $this->sheets = !empty($sheets) ? $this->sheetRepository->findByIds($sheets) : [];
        $this->slots = !empty($slots) ? $this->meetingSlotRepository->findByIds($slots) : [];
        $this->spots = !empty($spots) ? $this->spotRepository->getSpotsByIds($spots) : [];

        $concernedByMessagesRequestViews = array_merge($deletedRequestViews, $changedViews);
        $concernedByMessagesRequestIds = array_map(
            static function ($item) {
                return $item->requestId;
            },
            $concernedByMessagesRequestViews
        );
        $this->messages = $this->getLatestMessagesByRequest($concernedByMessagesRequestIds, $userSheets);
    }

    /**
     * @param Sheet[] $userSheets
     * @param array   $deletedRequests
     *
     * @return MeetingDeletedView[]
     */
    private function getDeletedMeeting(array &$userSheets, array &$deletedRequests): array
    {
        $deletedViews = [];

        if (!empty($deletedRequests)) {
            foreach ($deletedRequests as $key => $request) {
                $deletedViews[] = new MeetingDeletedView(
                    $this->getSheetMet($userSheets, $request), $request['request']
                );
            }
        }

        return $deletedViews;
    }

    /**
     * @param Sheet[] $userSheets
     * @param array   $addedRequest
     *
     * @return MeetingAddedView[]
     */
    private function getAddedMeeting(array &$userSheets, array &$addedRequest): array
    {
        $addedViews = [];

        if (!empty($addedRequest)) {
            foreach ($addedRequest as $key => $request) {
                $addedViews[] = new MeetingAddedView(
                    $this->getSheetMet($userSheets, $request),
                    $request['slot'],
                    $request['spot']
                );
            }
        }

        return $addedViews;
    }

    private function getChangedMeeting(array &$userSheets, array &$intersectVersion, array &$currentVersion): array
    {
        $changedViews = [];

        foreach ($intersectVersion as $key => $request) {
            try {
                if ($this->diffChecker->checkTwoVersion($currentVersion, $key, $request)) {
                    $changedViews[] = new MeetingMovedView(
                        $this->getSheetMet($userSheets, $request),
                        $currentVersion[$key]['slot'],
                        $currentVersion[$key]['spot'],
                        $request['request']
                    );
                }
            } catch (\InvalidArgumentException $exception) {
                continue;
            }
        }

        return $changedViews;
    }

    /**
     * @param Sheet[] $userSheets
     * @param array   $request
     *
     * @return int
     */
    private function getSheetMet(array &$userSheets, array &$request): int
    {
        return isset($userSheets[$request['fromSheet']]) ? $request['toSheet'] : $request['fromSheet'];
    }

    private function formatDay(\DateTimeInterface $date): string
    {
        return $this->format($this->dayFormatter, $date);
    }

    private function formatHour(\DateTimeInterface $hour): string
    {
        return $this->format($this->hourFormatter, $hour);
    }

    private function format(\IntlDateFormatter $dateFormatter, \DateTimeInterface $date): string
    {
        $dateFormatted = $dateFormatter->format($date);

        return \is_bool($dateFormatted) ? '' : $dateFormatted;
    }

    /**
     * @param int[]   $requestIds
     * @param Sheet[] $userSheets
     *
     * @return string[]
     */
    private function getLatestMessagesByRequest(array $requestIds, array $userSheets): array
    {
        // get latest message by request
        $messages = $this->messageRepository->getUpdateOrDeleteReasonMessageFromRequestIds($requestIds);

        $indexedMessages = [];
        foreach ($messages as $message) {
            // ignore message if it's mine
            if (\in_array($message->getFrom(), $userSheets, true)) {
                continue;
            }
            $indexedMessages[$message->getRequest()->getId()] = $message->getContent();
        }

        return $indexedMessages;
    }
}
