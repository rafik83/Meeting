<?php

namespace Proximum\Vimeet\Application\Query\User\Event\Contact;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class UserContactEvaluationStatsViewQueryHandler
{
    use TypeAndCategoriesTrait;

    /** @var ContactRepositoryInterface */
    private $contactRepository;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(
        ContactRepositoryInterface $contactRepository,
        MeetingRepositoryInterface $meetingRepository,
        TypeRepositoryInterface $typeRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->contactRepository = $contactRepository;
        $this->meetingRepository = $meetingRepository;
        $this->typeRepository = $typeRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @return UserContactEvaluationStatsView[]
     */
    public function handle(UserContactEvaluationStatsViewQuery $query): array
    {
        $contactEvaluationsViews = $this->getContactEvaluationStatsViews($query->event);

        $meetingsNumberByUser = $this->getMeetingsNumberByUserAndByEvent($query->event);
        $typeAndCategoriesTranslatedIndexedByTypeId = $this->getTypeAndCategoriesTranslatedIndexedByTypeId(
            $this->typeRepository,
            $query->event,
            $query->locale
        );

        $userSheetsViews = $this->userRepository->getUserSheetsViewsByEvent($query->event);

        /** @var UserContactEvaluationStatsView[] $userContactEvaluationStatsViews */
        $userContactEvaluationStatsViews = [];

        foreach ($userSheetsViews as $userSheetsView) {
            $userId = $userSheetsView->getUserId();

            if (!isset($meetingsNumberByUser[$userId])) {
                continue;
            }

            if (isset($userContactEvaluationStatsViews[$userId])) {
                $userContactEvaluationStatsViews[$userId]->addSheet(
                    $userSheetsView->getSheetId(),
                    $userSheetsView->getSheetTitle()
                );

                continue;
            }

            $contactEvaluationsView = $contactEvaluationsViews[$userId] ?? new ContactEvaluationsView($userId);
            $meetingsNumber = $meetingsNumberByUser[$userId] ?? 0;

            $userContactEvaluationStatsViews[$userId] = new UserContactEvaluationStatsView(
                $userId,
                $userSheetsView->getFirstName(),
                $userSheetsView->getLastName(),
                $typeAndCategoriesTranslatedIndexedByTypeId[$userSheetsView->getTypeId()]->getTypeTitle(),
                $typeAndCategoriesTranslatedIndexedByTypeId[$userSheetsView->getTypeId()]->getCategoriesTitle(),
                $userSheetsView->getSheetId(),
                $userSheetsView->getSheetTitle(),
                $meetingsNumber,
                $contactEvaluationsView->getContactsNumber(),
                $contactEvaluationsView->getContactsNumberByEvaluation(5),
                $contactEvaluationsView->getContactsNumberByEvaluation(4),
                $contactEvaluationsView->getContactsNumberByEvaluation(3),
                $contactEvaluationsView->getContactsNumberByEvaluation(2),
                $contactEvaluationsView->getContactsNumberByEvaluation(1),
                $meetingsNumber + $contactEvaluationsView->getContactsNumberNotEvaluated()
            );
        }

        return array_values($userContactEvaluationStatsViews);
    }

    /**
     * @param Event $event
     *
     * @return ContactEvaluationsView[] indexed by userId
     */
    private function getContactEvaluationStatsViews(Event $event): array
    {
        $contacts = $this->contactRepository->getByEvent($event);

        /** @var ContactEvaluationsView[] $contactEvaluationsViews */
        $contactEvaluationsViews = [];

        foreach ($contacts as $contact) {
            $userId = $contact->getUser()->getId();

            if (!isset($contactEvaluationsViews[$userId])) {
                $contactEvaluationsViews[$userId] = new ContactEvaluationsView($userId);
            }

            $contactEvaluationsViews[$userId]->addContact($contact->getEvaluation(), $contact->isScanned());
        }

        return $contactEvaluationsViews;
    }

    /**
     * @param Event $event
     *
     * @return array of meetings number indexed by userId [$userId => $meetingsNumber]
     */
    private function getMeetingsNumberByUserAndByEvent(Event $event): array
    {
        $meetings = $this->meetingRepository->getMeetingAndParticipantsByEvent($event);

        $meetingsNumberByUser = [];

        foreach ($meetings as $meeting) {
            foreach ($meeting->getAllParticipants() as $participant) {
                $userId = $participant->getUser()->getId();

                if (isset($meetingsNumberByUser[$userId])) {
                    ++$meetingsNumberByUser[$userId];
                } else {
                    $meetingsNumberByUser[$userId] = 1;
                }
            }
        }

        return $meetingsNumberByUser;
    }
}
