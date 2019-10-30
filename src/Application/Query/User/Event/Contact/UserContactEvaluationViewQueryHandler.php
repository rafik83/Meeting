<?php

namespace Proximum\Vimeet\Application\Query\User\Event\Contact;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class UserContactEvaluationViewQueryHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var TypeRepositoryInterface */
    private $typeRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        TypeRepositoryInterface $typeRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->meetingRepository = $meetingRepository;
        $this->typeRepository = $typeRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @param UserContactEvaluationViewQuery $query
     *
     * @return UserContactEvaluationView[]
     */
    public function handle(UserContactEvaluationViewQuery $query): array
    {
        $meetingsNumberByUser = $this->getMeetingsNumberByUserAndByEvent($query->event);
        $typeAndCategoriesTranslatedIndexedByTypeId = $this->getTypeAndCategoriesTranslatedIndexedByTypeId(
            $query->event,
            $query->locale
        );

        $userSheetsViews = $this->userRepository->getUserSheetsViewsByEvent($query->event);

        /** @var UserContactEvaluationView[] $userContactEvaluationViews */
        $userContactEvaluationViews = [];

        foreach ($userSheetsViews as $userSheetsView) {
            $userId = $userSheetsView->getUserId();

            if (!isset($meetingsNumberByUser[$userId])) {
                continue;
            }

            if (isset($userContactEvaluationViews[$userId])) {
                $userContactEvaluationViews[$userId]->addSheet(
                    $userSheetsView->getSheetId(),
                    $userSheetsView->getSheetTitle()
                );

                continue;
            }

            $userContactEvaluationViews[$userId] = new UserContactEvaluationView(
                $userId,
                $userSheetsView->getFirstName(),
                $userSheetsView->getLastName(),
                $typeAndCategoriesTranslatedIndexedByTypeId[$userSheetsView->getTypeId()]->getTypeTitle(),
                $typeAndCategoriesTranslatedIndexedByTypeId[$userSheetsView->getTypeId()]->getCategoriesTitle(),
                $userSheetsView->getSheetId(),
                $userSheetsView->getSheetTitle(),
                $meetingsNumberByUser[$userId]
            );
        }

        return array_values($userContactEvaluationViews);
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

    /**
     * @param Event $event
     * @param string $locale
     *
     * @return TypeAndCategoriesTranslated[]
     */
    private function getTypeAndCategoriesTranslatedIndexedByTypeId(Event $event, string $locale): array
    {
        $types = $this->typeRepository->getTypesAndCategoriesTranslationsByEvent($event, $locale);
        $typeAndCategoriesTranslatedIndexedByTypeId = [];

        foreach ($types as $type) {
            $typeAndCategoriesTranslatedIndexedByTypeId[$type->getId()] = new TypeAndCategoriesTranslated(
                $type->getTitle($locale),
                $type->getCategoriesTitles($locale)
            );
        }

        return $typeAndCategoriesTranslatedIndexedByTypeId;
    }
}
