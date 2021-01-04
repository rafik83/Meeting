<?php

namespace Proximum\Vimeet\Application\Command\Participant\Export;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Adapter\SheetSearchAdapterInterface;
use Proximum\Vimeet\Domain\Event\ExtraData\Type;
use Proximum\Vimeet\Domain\Exception\Participant\Export\NoParticipantToExportException;
use Proximum\Vimeet\Domain\Model\Event\ExtraData;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class PrepareExportHandler
{
    /** @var SheetSearchAdapterInterface */
    private $sheetSearchAdapter;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var JobQueueInterface */
    private $jobQueue;

    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        SheetSearchAdapterInterface $sheetSearchAdapter,
        ParticipantRepositoryInterface $participantRepository,
        ExtraDataRepositoryInterface $extraDataRepository,
        JobQueueInterface $jobQueue,
        \DateTimeInterface $dateTime
    ) {
        $this->sheetSearchAdapter = $sheetSearchAdapter;
        $this->participantRepository = $participantRepository;
        $this->extraDataRepository = $extraDataRepository;
        $this->jobQueue = $jobQueue;
        $this->dateTime = $dateTime;
    }

    /**
     * @param PrepareExport $command
     *
     * @throws NoParticipantToExportException
     */
    public function handle(PrepareExport $command): void
    {
        $sheetIds = $this->sheetSearchAdapter->getSheetIds(
            $command->event,
            $command->filters,
            $command->locale,
            $command->condition
        );

        if (empty($sheetIds)) {
            throw new NoParticipantToExportException();
        }

        $participants = $this->participantRepository->getByEventAndSheetIds($command->event, $sheetIds);

        $participantIds = array_map(function (Participant $participant) {
            return $participant->getId();
        }, $participants);

        $extraData = new ExtraData(
            $command->event,
            Type::ADMIN_PARTICIPANT_IDS,
            implode(',', $participantIds),
            $this->dateTime
        );

        $this->extraDataRepository->add($extraData);

        $this->jobQueue->exportParticipantsForEvent($command->event, $command->admin, $command->locale, $extraData);
    }
}
