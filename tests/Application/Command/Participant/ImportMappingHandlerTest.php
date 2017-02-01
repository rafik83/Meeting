<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Application\Components\Import\ParticipantImportTag;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantImportedEvent;
use Proximum\Vimeet\Application\Serializer\Decoder\CsvDecoder;
use Proximum\Vimeet\Application\Serializer\Denormalizer\ParticipantDenormalizer;
use Proximum\Vimeet\Application\Serializer\Denormalizer\ParticipantImportLogger;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\ParticipantImport;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\ParticipantImportRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ImportMappingHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $datetime            = new \DateTime();
        $locale              = 'fr';
        $csvHeaders          = [];
        $registrationHeaders = [];

        $admin = new Admin('admin@vimeet.com', '', '', $locale, '', '', '', $datetime);
        $event = EventFactory::createEvent();
        $type  = new Type($event);

        $filename = 'import.csv';
        $csvData  = [];

        $translatorAdapter = $this->prophesize(TranslatorAdapter::class);

        $participantImport = new ParticipantImport($type, [], $datetime);
        $participantLogger = new ParticipantImportLogger($translatorAdapter->reveal());

        $command = new ImportMapping(
            $event,
            $type,
            $admin,
            $locale,
            $csvHeaders,
            $registrationHeaders
        );
        $command->mappings = [];

        // Mock
        $csvDecoder                  = $this->prophesize(CsvDecoder::class);
        $session                     = $this->prophesize(SessionInterface::class);
        $denormalizer                = $this->prophesize(ParticipantDenormalizer::class);
        $eventDispatcher             = $this->prophesize(DelayedEventDispatcher::class);
        $localFileStorage            = $this->prophesize(LocalFileStorageAdapter::class);
        $participantImportRepository = $this->prophesize(ParticipantImportRepositoryInterface::class);

        $session->get(ParticipantImportTag::PARTICIPANT_IMPORT_FILE)->shouldBeCalled()->willReturn($filename);

        $csvDecoder->decode($filename, CsvDecoder::FORMAT)->shouldBeCalled()->willReturn($csvData);

        $denormalizer->denormalize($csvData, Participant::class, ParticipantDenormalizer::FORMAT, [
            'csvHeaders'          => [],
            'registrationHeaders' => [],
            'mappings'            => [],
            'event'               => $event,
            'type'                => $type,
            'locale'              => $locale,
        ])->shouldBeCalled()->willReturn($participantLogger);

        $participantImportRepository->add(
            Argument::that(function (ParticipantImport $participantImport) {
                return true;
            })
        )->shouldBeCalled();

        $localFileStorage->remove("import.csv", true)->shouldBeCalled();

        $session->get(ParticipantImportTag::PARTICIPANT_IMPORT_FILE)->shouldBeCalled();

        $session->remove(ParticipantImportTag::PARTICIPANT_IMPORT_FILE)->shouldBeCalled();
        $session->remove(ParticipantImportTag::PARTICIPANT_IMPORT_CHARSET)->shouldBeCalled();

        $eventDispatcher->dispatch(Events::PARTICIPANT_IMPORTED,
            Argument::that(function (ParticipantImportedEvent $event) {
                return true;
            })
        )->shouldBeCalled();

        $session->set(ParticipantImportLogger::PARTICIPANT_IMPORT_ID, $participantImport->getId())->shouldBeCalled();

        $handler = new ImportMappingHandler(
            $csvDecoder->reveal(),
            $session->reveal(),
            $denormalizer->reveal(),
            $eventDispatcher->reveal(),
            $localFileStorage->reveal(),
            $datetime,
            $participantImportRepository->reveal()
        );

        $handler->handle($command);
    }
}
