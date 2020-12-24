<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Save\Query\CustomData;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\EventExtraParameter\MappingGetter;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData\HasUserSheetStateChangedQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Save\Query\CustomData\HasUserSheetStateChangedQueryHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Event\ExtraParameter\Type as EventExtraParameterType;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type as UserEventExtraDataType;

class HasUserSheetStateChangedQueryHandlerTest extends TestCase
{
    public function testNoSheetStateTagExistsInMapping(): void
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);

        $mappingGetter = $this->prophesize(MappingGetter::class);
        $mappingGetter
            ->getMapping(
                $event->reveal(),
                EventExtraParameterType::TYPE_LENI_DATA_MAPPING
            )
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $hasUserSheetStateChangedQueryHandler = new HasUserSheetStateChangedQueryHandler(
            $extraDataRepository->reveal(),
            $mappingGetter->reveal()
        );

        $this->assertFalse(
            $hasUserSheetStateChangedQueryHandler->handle(
                new HasUserSheetStateChangedQuery($event->reveal(), $user->reveal(), [])
            )
        );
    }

    public function testUserHasNotPreviousFingerprintAndIsAccepted(): void
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $event->reveal(),
                UserEventExtraDataType::LENI_FINGERPRINT,
                $user->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $mappingGetter = $this->prophesize(MappingGetter::class);
        $mappingGetter
            ->getMapping(
                $event->reveal(),
                EventExtraParameterType::TYPE_LENI_DATA_MAPPING
            )
            ->shouldBeCalled()
            ->willReturn(['states' => ['sheet_state' => 'ZL_MODERATION']])
        ;

        $hasUserSheetStateChangedQueryHandler = new HasUserSheetStateChangedQueryHandler(
            $extraDataRepository->reveal(),
            $mappingGetter->reveal()
        );

        $this->assertFalse(
            $hasUserSheetStateChangedQueryHandler->handle(
                new HasUserSheetStateChangedQuery($event->reveal(), $user->reveal(), ['ZL_MODERATION' => 'A'])
            )
        );
    }

    public function testUserHasNotPreviousFingerprintAndIsValidated(): void
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $event->reveal(),
                UserEventExtraDataType::LENI_FINGERPRINT,
                $user->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $mappingGetter = $this->prophesize(MappingGetter::class);
        $mappingGetter
            ->getMapping(
                $event->reveal(),
                EventExtraParameterType::TYPE_LENI_DATA_MAPPING
            )
            ->shouldBeCalled()
            ->willReturn(['states' => ['sheet_state' => 'ZL_MODERATION']])
        ;

        $hasUserSheetStateChangedQueryHandler = new HasUserSheetStateChangedQueryHandler(
            $extraDataRepository->reveal(),
            $mappingGetter->reveal()
        );

        $this->assertTrue(
            $hasUserSheetStateChangedQueryHandler->handle(
                new HasUserSheetStateChangedQuery($event->reveal(), $user->reveal(), ['ZL_MODERATION' => 'Y'])
            )
        );
    }

    public function testUserHasPreviousFingerprintAndStateHasNotChanged(): void
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $extraData = $this->prophesize(User\Event\ExtraData::class);
        $extraData->getValue()->willReturn('a:1:{s:13:"ZL_MODERATION";s:1:"Y";}'); // Validated

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $event->reveal(),
                UserEventExtraDataType::LENI_FINGERPRINT,
                $user->reveal()
            )
            ->shouldBeCalled()
            ->willReturn($extraData->reveal())
        ;

        $mappingGetter = $this->prophesize(MappingGetter::class);
        $mappingGetter
            ->getMapping(
                $event->reveal(),
                EventExtraParameterType::TYPE_LENI_DATA_MAPPING
            )
            ->shouldBeCalled()
            ->willReturn(['states' => ['sheet_state' => 'ZL_MODERATION']])
        ;

        $hasUserSheetStateChangedQueryHandler = new HasUserSheetStateChangedQueryHandler(
            $extraDataRepository->reveal(),
            $mappingGetter->reveal()
        );

        $this->assertFalse(
            $hasUserSheetStateChangedQueryHandler->handle(
                new HasUserSheetStateChangedQuery(
                    $event->reveal(),
                    $user->reveal(),
                    ['ZL_MODERATION' => 'Y'] // Validated
                )
            )
        );
    }

    public function testUserHasPreviousFingerprintAndStateHasChanged(): void
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $extraData = $this->prophesize(User\Event\ExtraData::class);
        $extraData->getValue()->willReturn('a:1:{s:13:"ZL_MODERATION";s:1:"A";}'); // Accepted

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $event->reveal(),
                UserEventExtraDataType::LENI_FINGERPRINT,
                $user->reveal()
            )
            ->shouldBeCalled()
            ->willReturn($extraData->reveal())
        ;

        $mappingGetter = $this->prophesize(MappingGetter::class);
        $mappingGetter
            ->getMapping(
                $event->reveal(),
                EventExtraParameterType::TYPE_LENI_DATA_MAPPING
            )
            ->shouldBeCalled()
            ->willReturn(['states' => ['sheet_state' => 'ZL_MODERATION']])
        ;

        $hasUserSheetStateChangedQueryHandler = new HasUserSheetStateChangedQueryHandler(
            $extraDataRepository->reveal(),
            $mappingGetter->reveal()
        );

        $this->assertTrue(
            $hasUserSheetStateChangedQueryHandler->handle(
                new HasUserSheetStateChangedQuery(
                    $event->reveal(),
                    $user->reveal(),
                    ['ZL_MODERATION' => 'Y'] // Validated
                )
            )
        );
    }

    public function testUserHasPreviousValidatedFingerprintAndStateHasChanged(): void
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);
        $extraData = $this->prophesize(User\Event\ExtraData::class);
        $extraData->getValue()->willReturn('a:1:{s:13:"ZL_MODERATION";s:1:"Y";}'); // Validated

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $event->reveal(),
                UserEventExtraDataType::LENI_FINGERPRINT,
                $user->reveal()
            )
            ->shouldBeCalled()
            ->willReturn($extraData->reveal())
        ;

        $mappingGetter = $this->prophesize(MappingGetter::class);
        $mappingGetter
            ->getMapping(
                $event->reveal(),
                EventExtraParameterType::TYPE_LENI_DATA_MAPPING
            )
            ->shouldBeCalled()
            ->willReturn(['states' => ['sheet_state' => 'ZL_MODERATION']])
        ;

        $hasUserSheetStateChangedQueryHandler = new HasUserSheetStateChangedQueryHandler(
            $extraDataRepository->reveal(),
            $mappingGetter->reveal()
        );

        $this->assertFalse(
            $hasUserSheetStateChangedQueryHandler->handle(
                new HasUserSheetStateChangedQuery(
                    $event->reveal(),
                    $user->reveal(),
                    ['ZL_MODERATION' => 'A'] // Accepted
                )
            )
        );
    }
}
