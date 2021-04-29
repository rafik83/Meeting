<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Get\Converter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipant;
use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipantHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Normalizer\LeniUserViewNormalizer;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\TemplateData\ParticipationTypeTemplateDataGetter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter\DataConverter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter\RawDataToParticipantConverter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter\TypeConverter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\LeniUserViewQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\LeniUserViewQueryHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\View\LeniUserView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;

class RawDataToParticipantConverterTest extends TestCase
{
    public function testConvert()
    {
        $dateTime = new \DateTime();

        $rawDataUser1 = [
            'Id' => 'user-id-1',
            'Email' => 'bruce@willis.usa',
            'Langue' => 'fr'
        ];

        $rawDataUser2 = [
            'Id' => 'user-id-2',
            'Email' => 'ronald@macdonald.food',
            'Langue' => 'en'
        ];

        $typeMappingEvent1 = ['type-mapping-event-1'];
        $customDataMappingEvent1 = ['custom-data-mapping-event-1'];

        $user2 = $this->prophesize(User::class);
        $participantForUser2 = $this->prophesize(Participant::class);
        $participantForUser2->getUser()->shouldBeCalled()->willReturn($user2->reveal());

        $event1 = $this->prophesize(Event::class);

        $event1type1 = $this->prophesize(Type::class);
        $event1type1->getId()->willReturn(111);

        $event1type2 = $this->prophesize(Type::class);
        $event1type2->getId()->willReturn(222);

        $typeConverter = $this->prophesize(TypeConverter::class);
        $typeConverter
            ->convert([$event1type1->reveal(), $event1type2->reveal()], $typeMappingEvent1, $rawDataUser1)
            ->shouldBeCalled()
            ->willReturn($event1type2->reveal())
        ;
        $typeConverter
            ->convert([$event1type1->reveal(), $event1type2->reveal()], $typeMappingEvent1, $rawDataUser2)
            ->shouldBeCalled()
            ->willReturn($event1type1->reveal())
        ;

        $registrationTemplateDataEvent1type1 = $this->prophesize(TemplateData::class);
        $registrationTemplateDataEvent1type2 = $this->prophesize(TemplateData::class);
        $sheetTemplateDataEvent1type1 = $this->prophesize(TemplateData::class);
        $sheetTemplateDataEvent1type2 = $this->prophesize(TemplateData::class);

        $dataConverter = $this->prophesize(DataConverter::class);
        $dataConverter
            ->convert($customDataMappingEvent1, $rawDataUser1)
            ->shouldBeCalled()
            ->willReturn(['user1-data-indexed-by-tag' => 'whatever'])
        ;
        $dataConverter
            ->convert($customDataMappingEvent1, $rawDataUser2)
            ->shouldBeCalled()
            ->willReturn(['user2-data-indexed-by-tag' => 'whatever'])
        ;

        $convertToParticipantHandler = $this->prophesize(ConvertToParticipantHandler::class);
        $convertToParticipantHandler
            ->handle(
                new ConvertToParticipant(
                    $event1->reveal(),
                    $event1type2->reveal(),
                    'bruce@willis.usa',
                    'fr',
                    ['user1-data-indexed-by-tag' => 'whatever'],
                    $registrationTemplateDataEvent1type2->reveal(),
                    $sheetTemplateDataEvent1type2->reveal(),
                    'leni_user_id'
                )
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;
        $convertToParticipantHandler
            ->handle(
                new ConvertToParticipant(
                    $event1->reveal(),
                    $event1type1->reveal(),
                    'ronald@macdonald.food',
                    'en',
                    ['user2-data-indexed-by-tag' => 'whatever'],
                    $registrationTemplateDataEvent1type1->reveal(),
                    $sheetTemplateDataEvent1type1->reveal(),
                    'leni_user_id'
                )
            )
            ->shouldBeCalled()
            ->willReturn($participantForUser2->reveal())
        ;

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->add(new ExtraData($user2->reveal(), $event1->reveal(), 'leni_user_id', 'user-id-2', $dateTime))
            ->shouldBeCalled()
        ;

        $participationTypeTemplateDataGetter = $this->prophesize(ParticipationTypeTemplateDataGetter::class);
        $participationTypeTemplateDataGetter
            ->getRegistrationTemplateDataByType($event1type1->reveal())
            ->shouldBeCalled()
            ->willReturn($registrationTemplateDataEvent1type1->reveal())
        ;
        $participationTypeTemplateDataGetter
            ->getRegistrationTemplateDataByType($event1type2->reveal())
            ->shouldBeCalled()
            ->willReturn($registrationTemplateDataEvent1type2->reveal())
        ;
        $participationTypeTemplateDataGetter
            ->getSheetTemplateDataByType($event1type1->reveal())
            ->shouldBeCalled()
            ->willReturn($sheetTemplateDataEvent1type1->reveal())
        ;
        $participationTypeTemplateDataGetter
            ->getSheetTemplateDataByType($event1type2->reveal())
            ->shouldBeCalled()
            ->willReturn($sheetTemplateDataEvent1type2->reveal())
        ;

        $leniUserView = $this->prophesize(LeniUserView::class);
        $leniUserViewQueryHandler = $this->prophesize(LeniUserViewQueryHandler::class);
        $leniUserViewQueryHandler
            ->handle(new LeniUserViewQuery($event1->reveal(), $user2->reveal(), null))
            ->shouldBeCalled()
            ->willReturn($leniUserView->reveal())
        ;

        $leniUserViewNormalizer = $this->prophesize(LeniUserViewNormalizer::class);
        $leniUserViewNormalizer
            ->normalize($leniUserView->reveal())
            ->shouldBeCalled()
            ->willReturn(['normalized' => 'data'])
        ;

        $extraDataRepository
            ->add(
                new ExtraData(
                    $user2->reveal(),
                    $event1->reveal(),
                    'leni_fingerprint',
                    'a:1:{s:10:"normalized";s:4:"data";}',
                    $dateTime
                )
            )
            ->shouldBeCalled()
        ;

        $rawDataToParticipantConverter = new RawDataToParticipantConverter(
            $convertToParticipantHandler->reveal(),
            $typeConverter->reveal(),
            $dataConverter->reveal(),
            $participationTypeTemplateDataGetter->reveal(),
            $extraDataRepository->reveal(),
            $leniUserViewQueryHandler->reveal(),
            $leniUserViewNormalizer->reveal(),
            $dateTime
        );
        $rawDataToParticipantConverter->convert(
            $event1->reveal(),
            [$event1type1->reveal(), $event1type2->reveal()],
            $typeMappingEvent1,
            $customDataMappingEvent1,
            $rawDataUser1
        );
        $rawDataToParticipantConverter->convert(
            $event1->reveal(),
            [$event1type1->reveal(), $event1type2->reveal()],
            $typeMappingEvent1,
            $customDataMappingEvent1,
            $rawDataUser2
        );
    }

    public function testConvertWithSheetState()
    {
        $dateTime = new \DateTime();

        $rawDataUser = [
            'Id' => 'user-id-2',
            'Email' => 'ronald@macdonald.food',
            'Langue' => 'en',
            'ZL_MODERATION' => 'Y' // should be converted to "validated" sheet state
        ];

        $typeMappingEvent = ['type-mapping-event-1'];
        $customDataMappingEvent = [
            'states' => ['sheet_state' => 'ZL_MODERATION'],
            'tags' => ['my_tag' => 'custom-data-mapping-event-1']
        ];

        $user = $this->prophesize(User::class);
        $participantForUser2 = $this->prophesize(Participant::class);
        $participantForUser2->getUser()->shouldBeCalled()->willReturn($user->reveal());

        $event = $this->prophesize(Event::class);

        $eventType1 = $this->prophesize(Type::class);
        $eventType1->getId()->willReturn(111);

        $eventType2 = $this->prophesize(Type::class);
        $eventType2->getId()->willReturn(222);

        $typeConverter = $this->prophesize(TypeConverter::class);
        $typeConverter
            ->convert([$eventType1->reveal(), $eventType2->reveal()], $typeMappingEvent, $rawDataUser)
            ->shouldBeCalled()
            ->willReturn($eventType1->reveal())
        ;

        $registrationTemplateDataEvent1type1 = $this->prophesize(TemplateData::class);
        $sheetTemplateDataEvent1type1 = $this->prophesize(TemplateData::class);

        $dataConverter = $this->prophesize(DataConverter::class);
        $dataConverter
            ->convert($customDataMappingEvent, $rawDataUser)
            ->shouldBeCalled()
            ->willReturn([
                'sheet_state' => 'Y',
                'user2-data-indexed-by-tag' => 'whatever',
            ])
        ;

        $convertToParticipantHandler = $this->prophesize(ConvertToParticipantHandler::class);
        $convertToParticipantHandler
            ->handle(
                new ConvertToParticipant(
                    $event->reveal(),
                    $eventType1->reveal(),
                    'ronald@macdonald.food',
                    'en',
                    [
                        'sheet_state' => 'Y',
                        'user2-data-indexed-by-tag' => 'whatever',
                    ],
                    $registrationTemplateDataEvent1type1->reveal(),
                    $sheetTemplateDataEvent1type1->reveal(),
                    'leni_user_id',
                    'validated' // Raw data contains "sheet_state" with "Y" which is equivalent to "validated"
                )
            )
            ->shouldBeCalled()
            ->willReturn($participantForUser2->reveal())
        ;

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->add(new ExtraData($user->reveal(), $event->reveal(), 'leni_user_id', 'user-id-2', $dateTime))
            ->shouldBeCalled()
        ;

        $participationTypeTemplateDataGetter = $this->prophesize(ParticipationTypeTemplateDataGetter::class);
        $participationTypeTemplateDataGetter
            ->getRegistrationTemplateDataByType($eventType1->reveal())
            ->shouldBeCalled()
            ->willReturn($registrationTemplateDataEvent1type1->reveal())
        ;
        $participationTypeTemplateDataGetter
            ->getSheetTemplateDataByType($eventType1->reveal())
            ->shouldBeCalled()
            ->willReturn($sheetTemplateDataEvent1type1->reveal())
        ;

        $leniUserView = $this->prophesize(LeniUserView::class);
        $leniUserViewQueryHandler = $this->prophesize(LeniUserViewQueryHandler::class);
        $leniUserViewQueryHandler
            ->handle(new LeniUserViewQuery($event->reveal(), $user->reveal(), null))
            ->shouldBeCalled()
            ->willReturn($leniUserView->reveal())
        ;

        $leniUserViewNormalizer = $this->prophesize(LeniUserViewNormalizer::class);
        $leniUserViewNormalizer
            ->normalize($leniUserView->reveal())
            ->shouldBeCalled()
            ->willReturn(['normalized' => 'data'])
        ;

        $extraDataRepository
            ->add(
                new ExtraData(
                    $user->reveal(),
                    $event->reveal(),
                    'leni_fingerprint',
                    'a:1:{s:10:"normalized";s:4:"data";}',
                    $dateTime
                )
            )
            ->shouldBeCalled()
        ;

        $rawDataToParticipantConverter = new RawDataToParticipantConverter(
            $convertToParticipantHandler->reveal(),
            $typeConverter->reveal(),
            $dataConverter->reveal(),
            $participationTypeTemplateDataGetter->reveal(),
            $extraDataRepository->reveal(),
            $leniUserViewQueryHandler->reveal(),
            $leniUserViewNormalizer->reveal(),
            $dateTime
        );
        $rawDataToParticipantConverter->convert(
            $event->reveal(),
            [$eventType1->reveal(), $eventType2->reveal()],
            $typeMappingEvent,
            $customDataMappingEvent,
            $rawDataUser
        );
    }
}
