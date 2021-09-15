<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Application\Components\Import\ParticipantImportTag;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantImportedEvent;
use Proximum\Vimeet\Application\Serializer\Denormalizer\ParticipantImportLogger;
use Proximum\Vimeet\Application\View\Participant\ImportMappingView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\ParticipantImport;
use Proximum\Vimeet\Domain\Repository\ParticipantImportRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;

class ImportMappingHandler
{
    /** @var SessionInterface */
    private $session;

    /** @var DelayedEventDispatcher */
    private $eventDispatcher;

    /** @var \DateTimeInterface */
    private $date;

    /** @var LocalFileStorageAdapter */
    private $localFileStorage;

    /** @var ParticipantImportRepositoryInterface */
    private $participantImportRepository;

    /** @var SerializerAdapterInterface */
    private $serializerAdapter;

    public function __construct(
        SerializerAdapterInterface $serializerAdapter,
        SessionInterface $session,
        DelayedEventDispatcher $eventDispatcher,
        LocalFileStorageAdapter $localFileStorage,
        \DateTimeInterface $date,
        ParticipantImportRepositoryInterface $participantImportRepository
    ) {
        $this->serializerAdapter = $serializerAdapter;
        $this->session  = $session;
        $this->eventDispatcher = $eventDispatcher;
        $this->date = $date;
        $this->localFileStorage = $localFileStorage;
        $this->participantImportRepository = $participantImportRepository;
    }

    public function handle(ImportMapping $importMapping): void
    {
        $filename = $this->session->get(ParticipantImportTag::PARTICIPANT_IMPORT_FILE);
        $mappings = $this->removeIgnoreFields($importMapping->getMappingsIndexedByFileHeader());

        /** @var ParticipantImportLogger $importLogger */
        $importLogger = $this->serializerAdapter->deserialize(
            file_get_contents($filename),
            Participant::class,
            'csv',
            [
                'csv_delimiter' => ';',
                'mappings' => $mappings,
                'event' => $importMapping->event,
                'type' => $importMapping->type,
                'locale' => $importMapping->locale,
                'allowMultiSheet' => $importMapping->importMappingView->allowMultiSheet,
            ]
        );

        $participantImport = new ParticipantImport(
            $importMapping->type,
            $importLogger->toArray(),
            $mappings,
            $this->date,
            $importMapping->importMappingView->savedImportMapping
        );

        $this->participantImportRepository->add($participantImport);

        $this->localFileStorage->remove(
            $this->session->get(ParticipantImportTag::PARTICIPANT_IMPORT_FILE),
            true
        );

        $this->session->remove(ParticipantImportTag::PARTICIPANT_IMPORT_FILE);
        $this->session->remove(ParticipantImportTag::PARTICIPANT_IMPORT_CHARSET);
        $this->session->remove(ParticipantImportTag::PARTICIPANT_IMPORT_SAVED_MAPPING);
        $this->session->remove(ParticipantImportTag::PARTICIPANT_IMPORT_ALLOW_MULTI_SHEET);

        $this->eventDispatcher->dispatch(Events::PARTICIPANT_IMPORTED, new ParticipantImportedEvent(
            $importMapping->admin,
            $importMapping->event,
            $this->date,
            $importLogger->getSheets()
        ));

        $this->session->set(ParticipantImportLogger::PARTICIPANT_IMPORT_ID, $participantImport->getId());
    }

    private function removeIgnoreFields(array $mappings): array
    {
        return array_filter($mappings, static function ($value) {
            return ParticipantImportTag::REGISTRATION_FIELD_IGNORE !== $value;
        });
    }

    private function getAssociatedMapping(
        array $mappings,
        ImportMappingView $importMappingView
    ): array {
        $associatedMapping = [];
        $csvHeaders = $importMappingView->fieldHeaders;

        foreach ($mappings as $fileColumn => $value) {
            if (isset($csvHeaders[$fileColumn])) {
                $associatedMapping[$csvHeaders[$fileColumn]] = $value;
            }
        }

        return $associatedMapping;
    }
}
