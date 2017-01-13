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
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Template\Validator\ObjectValidatorFactory;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ImportMappingHandler
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var CsvDecoder
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
     * ImportMappingHandler constructor.
     *
     * @param DecoderInterface $csvDecoder
     * @param SessionInterface $session
     * @param DenormalizerInterface $denormalizer
     * @param DelayedEventDispatcher $eventDispatcher
     * @param \DateTimeInterface $date
     */
    public function __construct(
        DecoderInterface $csvDecoder,
        SessionInterface $session,
        DenormalizerInterface $denormalizer,
        DelayedEventDispatcher $eventDispatcher,
        \DateTimeInterface $date
    ) {
        $this->session      = $session;
        $this->csvDecoder   = $csvDecoder;
        $this->denormalizer = $denormalizer;
        $this->eventDispatcher = $eventDispatcher;
        $this->date = $date;
    }

    /**
     * @param ImportMapping $importMapping
     */
    public function handle(ImportMapping $importMapping)
    {
        $filename = $this->session->get(ParticipantImportTag::PARTICIPANT_IMPORT_FILE);

        $csvData = $this->csvDecoder->decode($filename, 'csv');

        $this->denormalizer->denormalize($csvData, Participant::class, 'csv', [
            'csvHeaders'          => $importMapping->csvHeaders,
            'registrationHeaders' => $importMapping->registrationHeaders,
            'mappings'            => $this->removeIgnoreFields($importMapping->mappings),
            'event'               => $importMapping->event,
            'type'                => $importMapping->type,
            'locale'              => $importMapping->locale,
        ]);

        $this->eventDispatcher->dispatch(Events::PARTICIPANT_IMPORTED, new ParticipantImportedEvent(
            $importMapping->admin,
            $importMapping->event,
            $this->date
        ));

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
