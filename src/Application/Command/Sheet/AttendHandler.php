<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Components\Sheet\HappeningParticipation\EnableDisableManager;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class AttendHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var EnableDisableManager */
    private $happeningsEnableDisableManager;

    /**
     * @param MeetingRepositoryInterface $meetingRepository
     * @param SheetRepositoryInterface   $sheetRepository
     * @param EnableDisableManager       $happeningsEnableDisableManager
     */
    public function __construct(
        MeetingRepositoryInterface $meetingRepository,
        SheetRepositoryInterface $sheetRepository,
        EnableDisableManager $happeningsEnableDisableManager
    ) {
        $this->meetingRepository = $meetingRepository;
        $this->sheetRepository   = $sheetRepository;
        $this->happeningsEnableDisableManager = $happeningsEnableDisableManager;
    }

    /**
     * @param Attend $attend
     */
    public function handle(Attend $attend)
    {
        if (false === $attend->attend) {
            $this->meetingRepository->removeMeetingOfSheet($attend->sheet);

            $this->happeningsEnableDisableManager->update(
                $attend->sheet,
                EnableDisableManager::DISABLE_HAPPENING_PARTICIPATION
            );
        } else {
            $this->happeningsEnableDisableManager->update(
                $attend->sheet,
                EnableDisableManager::ENABLE_HAPPENING_PARTICIPATION
            );
        }

        $attend->sheet->setAttendance($attend->attend);

        $this->sheetRepository->set($attend->sheet);
    }
}
