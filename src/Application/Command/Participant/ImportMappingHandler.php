<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Application\Components\Import\ParticipantImportTag;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantImportedEvent;
use Proximum\Vimeet\Application\Serializer\Decoder\CsvDecoder;
use Proximum\Vimeet\Application\Serializer\Denormalizer\ParticipantDenormalizer;
use Proximum\Vimeet\Application\Serializer\Denormalizer\ParticipantImportLogger;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\ParticipantImport;
use Proximum\Vimeet\Domain\Repository\ParticipantImportRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ImportMappingHandler
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var DecoderInterface
     */
    private $csvDecoder;

    /**
     * @var DenormalizerInterface
     */
    private $denormalizer;

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
     * ImportMappingHandler constructor.
     *
     * @param DecoderInterface                     $csvDecoder
     * @param SessionInterface                     $session
     * @param DenormalizerInterface                $denormalizer
     * @param DelayedEventDispatcher               $eventDispatcher
     * @param LocalFileStorageAdapter              $localFileStorage
     * @param \DateTimeInterface                   $date
     * @param ParticipantImportRepositoryInterface $participantImportRepository
     */
    public function __construct(
        DecoderInterface $csvDecoder,
        SessionInterface $session,
        DenormalizerInterface $denormalizer,
        DelayedEventDispatcher $eventDispatcher,
        LocalFileStorageAdapter $localFileStorage,
        \DateTimeInterface $date,
        ParticipantImportRepositoryInterface $participantImportRepository
    ) {
        $this->session                     = $session;
        $this->csvDecoder                  = $csvDecoder;
        $this->denormalizer                = $denormalizer;
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

        $csvData = $this->csvDecoder->decode($filename, CsvDecoder::FORMAT);

        $importLogger = $this->denormalizer->denormalize($csvData, Participant::class, ParticipantDenormalizer::FORMAT, [
            'csvHeaders'          => $importMapping->csvHeaders,
            'registrationHeaders' => $importMapping->registrationHeaders,
            'mappings'            => $this->removeIgnoreFields($importMapping->mappings),
            'event'               => $importMapping->event,
            'type'                => $importMapping->type,
            'locale'              => $importMapping->locale,
        ]);

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
            return $value != ParticipantImportTag::REGISTRATION_FIELD_IGNORE;
        });
    }
}
