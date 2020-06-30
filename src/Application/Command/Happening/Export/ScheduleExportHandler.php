<?php

namespace Proximum\Vimeet\Application\Command\Happening\Export;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Exception\Happening\EmptyHappeningParticipationException;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;

class ScheduleExportHandler
{
    /** @var HappeningParticipationRepositoryInterface */
    private $happeningParticipationRepository;

    /** @var JobQueueInterface */
    private $jobQueue;

    public function __construct(HappeningParticipationRepositoryInterface $happeningParticipationRepository, JobQueueInterface $jobQueue)
    {
        $this->happeningParticipationRepository = $happeningParticipationRepository;
        $this->jobQueue = $jobQueue;
    }

    /**
     * @throws EmptyHappeningParticipationException
     */
    public function handle(ScheduleExport $command): void
    {
        if (!$this->happeningParticipationRepository->hasHappeningParticipant($command->event)) {
            throw new EmptyHappeningParticipationException('Nothing to export');
        }

        $this->jobQueue->exportHappeningParticipants($command->event, $command->admin, $command->locale);
    }
}
