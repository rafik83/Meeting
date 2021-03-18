<?php


namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ScanRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Scan\Type;

class CanEvaluateHappening
{
    private SheetRepositoryInterface $sheetRepository;

    private ScanRepositoryInterface $scanRepository;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        ScanRepositoryInterface $scanRepository
    )
    {
        $this->sheetRepository = $sheetRepository;
        $this->scanRepository = $scanRepository;
    }

    public function isSatisfiableBy(Happening $happening, User $user): bool
    {

        if (!$happening->mustEvaluateHappening()) {
            return false;
        }

        if ($happening->hasSpeaker($user)) {
            return false;
        }

        $hasUserBeenScannedForHappening = $this->scanRepository->hasScanForUserEventTypeAndObjectId(
            $user,
            $happening->getEvent(),
            Type::TYPE_HAPPENING_ENTRANCE,
            $happening->getId()
        );

        if (!$hasUserBeenScannedForHappening) {
            return false;
        }

        $userSheets = $this->sheetRepository->getAllSheetsByUserAndEvent($user, $happening->getEvent());

        foreach ($happening->getSpeakers() as $speaker) {
            if (null === $speaker->getUser()) {
                continue;
            }
            $speakerSheets = $this->sheetRepository->getAllSheetsByUserAndEvent($speaker->getUser(), $happening->getEvent());

            $result = array_intersect($userSheets, $speakerSheets);

            if (!empty ($result)) {
                return false;
            }
        }
        return true;

    }

}
