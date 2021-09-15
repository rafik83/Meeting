<?php

namespace Proximum\Vimeet\Application\Query\Participant\Import;

use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Application\Serializer\Denormalizer\ParticipantImportLogger;
use Proximum\Vimeet\Domain\Repository\ParticipantImportRepositoryInterface;
use Proximum\Vimeet\Domain\View\Normalizer\ParticipantDenormalizerView;

class ImportResultViewQueryHandler
{
    /** @var SessionInterface */
    private $session;

    /** @var ParticipantImportRepositoryInterface */
    private $participantImportRepository;

    public function __construct(
        SessionInterface $session,
        ParticipantImportRepositoryInterface $participantImportRepository
    ) {
        $this->session = $session;
        $this->participantImportRepository = $participantImportRepository;
    }

    public function handle(): ParticipantDenormalizerView
    {
        $participantImportId = $this->session->get(ParticipantImportLogger::PARTICIPANT_IMPORT_ID);

        $participantImport = $this->participantImportRepository->findById($participantImportId);

        $loggerData = $participantImport->getResult();

        return new ParticipantDenormalizerView(
            $participantImport,
            $loggerData[ParticipantImportLogger::EXISTING_PARTICIPATIONS] ?? 0,
            $loggerData[ParticipantImportLogger::FILE_PARTICIPATIONS] ?? 0,
            $loggerData[ParticipantImportLogger::CREATED_SHEETS] ?? 0,
            $loggerData[ParticipantImportLogger::CREATED_USERS] ?? 0,
            $loggerData[ParticipantImportLogger::IMPORT_ERRORS] ?? 0
        );
    }
}
