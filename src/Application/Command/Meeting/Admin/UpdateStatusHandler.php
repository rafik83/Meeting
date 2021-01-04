<?php

namespace Proximum\Vimeet\Application\Command\Meeting\Admin;

use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;

class UpdateStatusHandler
{
    /** @var MeetingRepositoryInterface */
    private $meetingRepository;

    public function __construct(MeetingRepositoryInterface $meetingRepository)
    {
        $this->meetingRepository = $meetingRepository;
    }

    public function handle(UpdateStatus $command): void
    {
        $command->meeting->setStatus($command->value);

        $this->meetingRepository->set($command->meeting);
    }
}
