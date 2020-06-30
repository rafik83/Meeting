<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Meeting\MeetingSheetListView;
use Proximum\Vimeet\Application\View\Meeting\MeetingSheetView;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class MeetingSheetViewQueryHandler
{
    /** @var RequestRepositoryInterface */
    private $requestRepository;

    /** @var ParticipantsViewQueryHandler */
    private $participantsViewQueryHandler;

    /** @var SheetInfoGuesser */
    private $sheetInfoGuesser;

    /** @var ContactRepositoryInterface */
    private $contactRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    public function __construct(
        ContactRepositoryInterface $contactRepository,
        RequestRepositoryInterface $requestRepository,
        SheetRepositoryInterface $sheetRepository,
        ParticipantsViewQueryHandler $participantsViewQueryHandler,
        SheetInfoGuesser $sheetInfoGuesser
    ) {
        $this->contactRepository = $contactRepository;
        $this->requestRepository = $requestRepository;
        $this->sheetRepository = $sheetRepository;
        $this->participantsViewQueryHandler = $participantsViewQueryHandler;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
    }

    public function handle(MeetingSheetViewQuery $query): MeetingSheetListView
    {
        $contacts = $this->contactRepository->findByEventAndUsers($query->event, $query->sheet->getUsers());
        $meetingSheetViews = $this->getFromApprovedRequests($query->sheet, $query->locale, $contacts);
        $meetingSheetViews = $this->addFromContacts($meetingSheetViews, $query->event, $contacts, $query->locale);

        return new MeetingSheetListView($meetingSheetViews, $query->event->getTitle());
    }

    /**
     * @param Sheet  $sheet
     * @param string $locale
     * @param array  $contacts
     *
     * @return MeetingSheetView[]
     */
    private function getFromApprovedRequests(Sheet $sheet, string $locale, array $contacts): array
    {
        $meetingSheetViews = [];

        foreach ($this->requestRepository->findApproved($sheet) as $meetingRequest) {
            $sheetMet = $meetingRequest->getSheetMet($sheet);
            $meetingSheetViews[$sheetMet->getId()] = $this->createMeetingSheetView(
                $sheetMet,
                $sheetMet->getParticipantsArray(),
                $locale,
                true,
                $contacts
            );
        }

        return $meetingSheetViews;
    }

    /**
     * @param MeetingSheetView[] $meetingSheetViews
     * @param Event              $event
     * @param array              $contacts
     * @param string             $locale
     *
     * @return MeetingSheetView[]
     */
    private function addFromContacts(array $meetingSheetViews, Event $event, array $contacts, string $locale): array
    {
        $participantsBySheet = [];
        $sheets = [];

        foreach ($contacts as $contact) {
            $sheetsOfContact = $this->sheetRepository->getSheetsByUserAndEvent($contact->getContact(), $event);
            $participantOfContact = $this->getParticipantFromSheets($sheetsOfContact, $contact->getContact());

            if (!$participantOfContact instanceof Participant) {
                continue;
            }

            $sheet = $participantOfContact->getSheet();
            $sheetId = $sheet->getId();

            if (isset($meetingSheetViews[$sheetId])) {
                continue;
            }

            $sheets[$sheetId] = $sheet;

            if (isset($participantsBySheet[$sheetId])) {
                $participantsBySheet[$sheetId][] = $participantOfContact;

                continue;
            }

            $participantsBySheet[$sheetId] = [$participantOfContact];
        }

        foreach ($sheets as $sheetId => $sheet) {
            $meetingSheetViews[$sheet->getId()] = $this->createMeetingSheetView(
                $sheet,
                $participantsBySheet[$sheetId],
                $locale,
                false,
                $contacts
            );
        }

        return $meetingSheetViews;
    }

    /**
     * @param Sheet         $sheet
     * @param Participant[] $participants
     * @param string        $locale
     * @param bool          $hasApprovedMeetingRequestWith
     * @param Contact[]         $contacts
     *
     * @return MeetingSheetView
     */
    private function createMeetingSheetView(
        Sheet $sheet,
        array $participants,
        string $locale,
        bool $hasApprovedMeetingRequestWith,
        array $contacts
    ): MeetingSheetView {
        $sheetTags = $this->sheetInfoGuesser->guessSheetInfos($sheet, $locale);

        return new MeetingSheetView(
            $sheet->getTitle(),
            $sheetTags[Tag::SHEET_ORGANIZATION_CATEGORY],
            $sheetTags[Tag::SHEET_ORGANIZATION_TURNOVER],
            $sheetTags[Tag::SHEET_ORGANIZATION_STAFF],
            $sheetTags[Tag::SHEET_WEBSITE],
            $sheetTags[Tag::SHEET_ADDRESS],
            $sheetTags[Tag::SHEET_ZIPCODE],
            $sheetTags[Tag::SHEET_CITY],
            $sheetTags[Tag::SHEET_COUNTRY],
            $sheet->getType()->getTitle($locale),
            $hasApprovedMeetingRequestWith,
            $this->participantsViewQueryHandler->handle(
                new ParticipantsViewQuery($participants, $locale, $contacts)
            )
        );
    }

    private function getParticipantFromSheets(array $sheets, User $user): ?Participant
    {
        foreach ($sheets as $sheet) {
            $participant = $sheet->getUserParticipant($user);

            if (null !== $participant) {
                return $participant;
            }
        }

        return null;
    }
}
