<?php

namespace Proximum\Vimeet\Domain\Sheet\Availability;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class ConfirmationCalculator
{
    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /**
     * @param ExtraDataRepositoryInterface $extraDataRepository
     */
    public function __construct(ExtraDataRepositoryInterface $extraDataRepository)
    {
        $this->extraDataRepository = $extraDataRepository;
    }

    /**
     * @param Sheet $sheet
     *
     * @return string
     */
    public function getConfirmationStatusForSheet(Sheet $sheet): string
    {
        $event = $sheet->getEvent();

        $numberFound = 0;

        foreach ($sheet->getParticipantsArray() as $participant) {
            $extraData = $this->extraDataRepository->getExtraDataForEventNameAndUser(
                $event,
                Type::AVAILABILITY_CONFIRMATION,
                $participant->getUser()
            );

            if ($extraData instanceof ExtraData) {
                ++$numberFound;
            }

            // Early return if the extraData returned is null and we already found one confirmed
            if (null === $extraData && $numberFound > 0) {
                return ConfirmationStatus::AT_LEAST_ONE_CONFIRMED;
            }
        }

        if (0 === $numberFound) {
            return ConfirmationStatus::NONE_CONFIRMED;
        }

        return $numberFound === $sheet->countParticipants()
            ? ConfirmationStatus::ALL_CONFIRMED
            : ConfirmationStatus::AT_LEAST_ONE_CONFIRMED
        ;
    }
}
