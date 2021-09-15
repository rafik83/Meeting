<?php

namespace Proximum\Vimeet\Domain\Sheet\Phone;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User\UserEventPhone;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Domain\UserEvent\UserEventPhoneChecker;

/**
 * This class calculate the validation status of the aggregate of a Sheet for the User Event Phone
 *
 * @see ValidationStatus for the different possible status
 */
class ValidationCalculator
{
    /** @var TipRepositoryInterface */
    private $tipRepository;

    /** @var array of type id and bool if allowed */
    private $typesThatAllowPhones;

    /** @var UserEventPhoneChecker */
    private $userEventPhoneChecker;

    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /**
     * @param TipRepositoryInterface     $tipRepository
     * @param UserEventPhoneChecker      $userEventPhoneChecker
     * @param MeetingRepositoryInterface $meetingRepository
     */
    public function __construct(
        TipRepositoryInterface $tipRepository,
        UserEventPhoneChecker $userEventPhoneChecker,
        MeetingRepositoryInterface $meetingRepository
    ) {
        $this->tipRepository = $tipRepository;
        $this->userEventPhoneChecker = $userEventPhoneChecker;
        $this->meetingRepository = $meetingRepository;
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getValidationStatusForSheet(Sheet $sheet): string
    {
        if (!$this->doesTypeAllowPhoneValidation($sheet->getEvent(), $sheet->getType())) {
            return ValidationStatus::NOT_CONCERNED;
        }

        $users = array_map(function (Participant $participant) {
            return $participant->getUser();
        }, $sheet->getParticipants()->toArray());

        $event = $sheet->getEvent();

        $concerned    = 0;
        $confirmed    = 0;
        $notConfirmed = 0;

        foreach ($users as $user) {
            $userPhone = $this->userEventPhoneChecker->getValidatedUserEventPhone($user, $event);

            $hasMeeting = $this->meetingRepository->hasMeetingForUserAndEvent($user, $event);

            if (true === $hasMeeting) {
                ++$concerned;

                // If UserEventPhone is null (meaning not sent), the user is considered notConfirmed
                if ($userPhone instanceof UserEventPhone && $userPhone->isValidated()) {
                    ++$confirmed;

                    continue;
                }

                ++$notConfirmed;
            }
        }

        if (0 !== $concerned) {
            if ($concerned === $confirmed) {
                return ValidationStatus::ALL_CONFIRMED;
            } elseif ($concerned === $notConfirmed) {
                return ValidationStatus::NONE_CONFIRMED;
            }

            return ValidationStatus::PARTLY_CONFIRMED;
        }

        return ValidationStatus::NOT_CONCERNED;
    }

    /**
     * @param Event $event
     * @param Type  $type
     *
     * @return bool
     */
    public function doesTypeAllowPhoneValidation(Event $event, Type $type): bool
    {
        if (isset($this->typesThatAllowPhones[$type->getId()])) {
            return $this->typesThatAllowPhones[$type->getId()];
        }

        $this->typesThatAllowPhones[$type->getId()] = $this->tipRepository
            ->isConfirmationPhoneEnabled($event, $type)
        ;

        return $this->typesThatAllowPhones[$type->getId()];
    }

    /**
     * @param Event  $event
     * @param Type[] $types
     */
    public function preloadTypeThatAllowPhones(Event $event, array &$types)
    {
        foreach ($types as $type) {
            $allowPhone = $this->tipRepository->isConfirmationPhoneEnabled($event, $type);

            $this->typesThatAllowPhones[$type->getId()] = $allowPhone;
        }
    }
}
