<?php

namespace Proximum\Vimeet\Domain\Sheet;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;

/**
 * ParticipantFinder add method to find the participant of a sheet with its id
 */
final class ParticipantFinder
{
    /**
     * @param Sheet $sheet
     * @param int   $id
     *
     * @return bool
     */
    public static function hasParticipantWithId(Sheet $sheet, $id)
    {
        return null !== self::getParticipantWithId($sheet, $id);
    }

    /**
     * @param Sheet $sheet
     * @param int   $id
     *
     * @return null|Participant
     */
    public static function getParticipantWithId(Sheet $sheet, $id)
    {
        $participants = $sheet->getParticipants()->toArray();

        /** @var Participant $participant */
        foreach ($participants as $participant) {
            if ($id === $participant->getId()) {
                return $participant;
            }
        }

        return null;
    }

    /**
     * @param Sheet $sheet
     * @param int   $userId
     *
     * @return null|Participant
     */
    public static function getParticipantWithUserId(Sheet $sheet, int $userId): ?Participant
    {
        $participants = $sheet->hasLinkedSheets()
            ? $sheet->getLinkedSheetsParticipants()
            : $sheet->getParticipantsArray()
        ;

        /** @var Participant $participant */
        foreach ($participants as $participant) {
            if ($userId === $participant->getUser()->getId()) {
                return $participant;
            }
        }

        return null;
    }
}
