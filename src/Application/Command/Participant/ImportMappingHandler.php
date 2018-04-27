<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Application\Components\Import\ParticipantImportTag;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantImportedEvent;
use Proximum\Vimeet\Application\Serializer\Denormalizer\ParticipantImportLogger;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\ParticipantImport;
use Proximum\Vimeet\Domain\Repository\ParticipantImportRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;

class ImportMappingHandler
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var DelayedEventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var \DateTimeInterface
     */
    private $date;

    /**
     * @var LocalFileStorageAdapter
     */
    private $localFileStorage;

    /**
     * @var ParticipantImportRepositoryInterface
     */
    private $participantImportRepository;

    /**
     * @var SerializerAdapterInterface
     */
    private $serializerAdapter;

    /**
     * @param SerializerAdapterInterface           $serializerAdapter
     * @param SessionInterface                     $session
     * @param DelayedEventDispatcher               $eventDispatcher
     * @param LocalFileStorageAdapter              $localFileStorage
     * @param \DateTimeInterface                   $date
     * @param ParticipantImportRepositoryInterface $participantImportRepository
     */
    public function __construct(
        SerializerAdapterInterface $serializerAdapter,
        SessionInterface $session,
        DelayedEventDispatcher $eventDispatcher,
        LocalFileStorageAdapter $localFileStorage,
        \DateTimeInterface $date,
        ParticipantImportRepositoryInterface $participantImportRepository
    ) {
        $this->serializerAdapter           = $serializerAdapter;
        $this->session                     = $session;
        $this->eventDispatcher             = $eventDispatcher;
        $this->date                        = $date;
        $this->localFileStorage            = $localFileStorage;
        $this->participantImportRepository = $participantImportRepository;
    }

    /**
     * @param ImportMapping $importMapping
     */
    public function handle(ImportMapping $importMapping)
    {
        $filename = $this->session->get(ParticipantImportTag::PARTICIPANT_IMPORT_FILE);

        /** @var ParticipantImportLogger $importLogger */
        $importLogger = $this->serializerAdapter->deserialize(
            file_get_contents($filename),
            Participant::class,
            'csv',
            [
                'csv_delimiter' => ';',
                'mappings'      => $this->removeIgnoreFields($importMapping->getMappings()),
                'event'         => $importMapping->event,
                'type'          => $importMapping->type,
                'locale'        => $importMapping->locale,
            ]
        );

        $participantImport = new ParticipantImport(
            $importMapping->type,
            $importLogger->toArray(),
            $this->date
        );

        $this->participantImportRepository->add($participantImport);

        $this->localFileStorage->remove(
            $this->session->get(ParticipantImportTag::PARTICIPANT_IMPORT_FILE),
            true
        );

        $this->session->remove(ParticipantImportTag::PARTICIPANT_IMPORT_FILE);
        $this->session->remove(ParticipantImportTag::PARTICIPANT_IMPORT_CHARSET);

        $this->eventDispatcher->dispatch(Events::PARTICIPANT_IMPORTED, new ParticipantImportedEvent(
            $importMapping->admin,
            $importMapping->event,
            $this->date,
            $importLogger->getSheets()
        ));

        $this->session->set(ParticipantImportLogger::PARTICIPANT_IMPORT_ID, $participantImport->getId());
    }

    /**
     * @param array $mappings
     *
     * @return array
     */
    private function removeIgnoreFields(array $mappings)
    {
        return array_filter($mappings, function ($value) {
            return ParticipantImportTag::REGISTRATION_FIELD_IGNORE != $value;
        });
    }
}
