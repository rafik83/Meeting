<?php

namespace Proximum\Vimeet\Tests\Application\Command\Participant\Import;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\SessionInterface;
use Proximum\Vimeet\Application\Command\Participant\ImportMapping;
use Proximum\Vimeet\Application\Command\Participant\ImportMappingHandler;
use Proximum\Vimeet\Application\Components\Import\ParticipantImportTag;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ParticipantImportedEvent;
use Proximum\Vimeet\Application\Serializer\Denormalizer\ParticipantImportLogger;
use Proximum\Vimeet\Application\View\Participant\ImportMappingView;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\ParticipantImport;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\ParticipantImportRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\DelayedEventDispatcher;
use Proximum\Vimeet\Infrastructure\Adapter\LocalFileStorageAdapter;

class ImportMappingHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $datetime = new \DateTime();
        $locale   = 'fr';

        $registrationHeaders = [
            'participant_import.field.ignore',
            'participant_import.field.mail',
            'firstname',
            'lastname',
            'position',
            'company',
            'mobile',
            'city',
            'zipcode',
            'address',
            'country',
        ];

        $csvHeaders = [
            0 => 'Nom participant',
            1 => 'Prénom participant',
            2 => 'Société Acheteur',
            3 => 'Fonction Pro Acheteur',
            4 => 'E-mail Acheteur',
            5 => 'Mobile',
            6 => 'Tarif',
            7 => 'Facture - Ville',
            8 => 'Pays Acheteur',
            9 => 'Facture - Code postal',
            10 => 'Facture - Adresse',
        ];

        $mapping = [
            0 => 'lastname',
            1 => 'firstname',
            2 => 'company',
            3 => 'participant_import.field.ignore',
            4 => 'participant_import.field.mail',
            5 => 'mobile',
            6 => 'participant_import.field.ignore',
            7 => 'city',
            8 => 'country',
            9 => 'zipcode',
            10 => 'address',
        ];

        $admin = $this->prophesize(Admin::class);
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);

        $filename = __DIR__ . '/import_participants.csv';

        // Mock
        $serializerAdapter           = $this->prophesize(SerializerAdapterInterface::class);
        $session                     = $this->prophesize(SessionInterface::class);
        $eventDispatcher             = $this->prophesize(DelayedEventDispatcher::class);
        $localFileStorage            = $this->prophesize(LocalFileStorageAdapter::class);
        $participantImportRepository = $this->prophesize(ParticipantImportRepositoryInterface::class);
        $participantImportLogger     = $this->prophesize(ParticipantImportLogger::class);

        $participantImportLogger->getSheets()->willReturn([]);
        $participantImportLogger->toArray()->willReturn([]);

        $command = new ImportMapping(
            $event->reveal(),
            $type->reveal(),
            $admin->reveal(),
            $locale,
            new ImportMappingView($csvHeaders, $registrationHeaders, false, null),
            null
        );

        $command->setMappings($mapping);

        $expectedReMapping = [
            'Nom participant' => 'lastname',
            'Prénom participant' => 'firstname',
            'Société Acheteur' => 'company',
            'E-mail Acheteur' => 'participant_import.field.mail',
            'Mobile' => 'mobile',
            'Pays Acheteur' => 'country',
            'Fonction Pro Acheteur' => 'participant_import.field.ignore',
            'Tarif' => 'participant_import.field.ignore',
            'Facture - Ville' => 'city',
            'Facture - Code postal' => 'zipcode',
            'Facture - Adresse' => 'address',
        ];

        $this->assertEquals($expectedReMapping, $command->getMappingsIndexedByFileHeader());

        $session->get(ParticipantImportTag::PARTICIPANT_IMPORT_FILE)->shouldBeCalled()->willReturn($filename);
        $localFileStorage->remove($filename, true)->shouldBeCalled();

        $session->get(ParticipantImportTag::PARTICIPANT_IMPORT_FILE)->shouldBeCalled();
        $session->remove(ParticipantImportTag::PARTICIPANT_IMPORT_FILE)->shouldBeCalled();
        $session->remove(ParticipantImportTag::PARTICIPANT_IMPORT_CHARSET)->shouldBeCalled();
        $session->remove(ParticipantImportTag::PARTICIPANT_IMPORT_SAVED_MAPPING)->shouldBeCalled();
        $session->remove(ParticipantImportTag::PARTICIPANT_IMPORT_ALLOW_MULTI_SHEET)->shouldBeCalled();
        $session->set(ParticipantImportLogger::PARTICIPANT_IMPORT_ID, Argument::any())->shouldBeCalled();

        $participantImport = new ParticipantImport(
            $type->reveal(),
            [],
            [
                'Nom participant' => 'lastname',
                'Prénom participant' => 'firstname',
                'Société Acheteur' => 'company',
                'E-mail Acheteur' => 'participant_import.field.mail',
                'Mobile' => 'mobile',
                'Pays Acheteur' => 'country',
                'Facture - Ville' => 'city',
                'Facture - Code postal' => 'zipcode',
                'Facture - Adresse' => 'address',
            ],
            $datetime,
            null
        );
        $participantImportRepository->add($participantImport)->shouldBeCalled();

        $eventDispatcher
            ->dispatch(
                Events::PARTICIPANT_IMPORTED,
                Argument::that(
                    static function (ParticipantImportedEvent $event) {
                        return true;
                    }
                )
            )
            ->shouldBeCalled()
        ;

        $serializerAdapter
            ->deserialize(
                Argument::type('string'),
                Participant::class,
                'csv',
                [
                    'csv_delimiter' => ';',
                    'mappings' => [
                        'Nom participant'       => 'lastname',
                        'Prénom participant'    => 'firstname',
                        'Société Acheteur'      => 'company',
                        'E-mail Acheteur'       => 'participant_import.field.mail',
                        'Mobile'                => 'mobile',
                        'Pays Acheteur'         => 'country',
                        'Facture - Ville'       => 'city',
                        'Facture - Code postal' => 'zipcode',
                        'Facture - Adresse'     => 'address',
                    ],
                    'event' => $event->reveal(),
                    'type' => $type->reveal(),
                    'locale' => 'fr',
                    'allowMultiSheet' => false,
                ]
            )
            ->shouldBeCalled()
            ->willReturn($participantImportLogger->reveal())
        ;

        $handler = new ImportMappingHandler(
            $serializerAdapter->reveal(),
            $session->reveal(),
            $eventDispatcher->reveal(),
            $localFileStorage->reveal(),
            $datetime,
            $participantImportRepository->reveal()
        );

        $handler->handle($command);
    }
}
