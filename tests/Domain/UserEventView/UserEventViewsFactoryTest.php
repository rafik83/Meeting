<?php

namespace Proximum\Vimeet\Tests\Domain\UserEventView;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Event\Filter\GetTemplateFiltersQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserEvent\UserEventViewRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Domain\UserEventView\UserEventView;
use Proximum\Vimeet\Domain\UserEventView\UserEventViewsFactory;
use Proximum\Vimeet\Domain\Repository\User as UserRepository;

class UserEventViewsFactoryTest extends TestCase
{
    public function testGetByEvent()
    {
        $participant1 = $this->prophesize(Participant::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getId()->shouldBeCalled()->willReturn(42);
        $sheet1->getParticipants()->shouldBeCalled()->willReturn([$participant1->reveal()]);

        $participant2 = $this->prophesize(Participant::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getId()->shouldBeCalled()->willReturn(43);
        $sheet2->getParticipants()->shouldBeCalled()->willReturn([$participant2->reveal()]);

        $participant3 = $this->prophesize(Participant::class);
        $sheet3 = $this->prophesize(Sheet::class);
        $sheet3->getId()->shouldBeCalled()->willReturn(1456);
        $sheet3->getParticipants()->shouldBeCalled()->willReturn([$participant3->reveal()]);

        $results = [
            [
                'sheetObject' => $sheet1->reveal(),
                'ownerId' => 33,
                'ownerEmail' => 'michel@example.net',
                'ownerFirstName' => 'Michel',
                'ownerLastName' => 'BLANC',
                'ownerLocale' => 'fr',
                'userId' => 78,
                'userEmail' => 'chloe@example.net',
                'userFirstName' => 'Chloé',
                'userLastName' => 'HENRY',
                'userLocale' => 'en',
            ],
            [
                'sheetObject' => $sheet2->reveal(),
                'ownerId' => 34,
                'ownerEmail' => 'julie@example.net',
                'ownerFirstName' => 'Julie',
                'ownerLastName' => 'DUPOND',
                'ownerLocale' => 'fr',
                'userId' => 33,
                'userEmail' => 'michel@example.net',
                'userFirstName' => 'Michel',
                'userLastName' => 'BLANC',
                'userLocale' => 'fr',
            ],
            [
                'sheetObject' => $sheet3->reveal(),
                'ownerId' => 99,
                'ownerEmail' => 'hello@example.net',
                'ownerFirstName' => null,
                'ownerLastName' => null,
                'ownerLocale' => 'en',
                'userId' => 99,
                'userEmail' => 'hello@example.net',
                'userFirstName' => null,
                'userLastName' => null,
                'userLocale' => 'en',
            ],
        ];

        $expectedUserEventViews = [
            33 => new UserEventView(
                777,
                33,
                'Michel',
                'BLANC',
                'michel@example.net',
                'fr',
                false,
                false,
                [
                    ['id' => 42],
                    ['id' => 43],
                ],
               []
            ),
            78 => new UserEventView(
                777,
                78,
                'Chloé',
                'HENRY',
                'chloe@example.net',
                'en',
                false,
                false,
                [
                    ['id' => 42],
                ],
                []
            ),
            34 => new UserEventView(
                777,
                34,
                'Julie',
                'DUPOND',
                'julie@example.net',
                'fr',
                false,
                true,
                [
                    ['id' => 43],
                ],
                []
            ),
            99 => new UserEventView(
                777,
                99,
                null,
                null,
                'hello@example.net',
                'en',
                true,
                false,
                [
                    ['id' => 1456],
                ],
                []
            ),
        ];

        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(777);

        $userEventViewRepository = $this->prophesize(UserEventViewRepositoryInterface::class);
        $userEventViewRepository->getByEvent($event->reveal())->shouldBeCalled()->willReturn($results);

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);

        $extraDataVisioUser99 = $this->prophesize(ExtraData::class);
        $extraDataRepository
            ->getExtraDataForEventIdAndNameIndexedByUserId(777, Type::IS_PARTICIPANT_VISIO)
            ->shouldBeCalled()
            ->willReturn([99 => $extraDataVisioUser99->reveal()])
        ;

        $extraDataVisioTestedUser34 = $this->prophesize(ExtraData::class);
        $extraDataRepository
            ->getExtraDataForEventIdAndNameIndexedByUserId(777, Type::VISIO_TESTED)
            ->shouldBeCalled()
            ->willReturn([34 => $extraDataVisioTestedUser34->reveal()])
        ;

        $queryBus = $this->prophesize(QueryBusInterface::class);

        $queryBus->handle(new GetTemplateFiltersQuery($event->reveal(), 'participant_data'))
            ->shouldBeCalled()
            ->willReturn([]);

        $formDataRepositoryInterface = $this->prophesize(UserRepository\FormDataRepositoryInterface::class);
        $formDataRepositoryInterface
            ->getDataByEventIdAndUserId(777, 33)
            ->shouldBeCalled()
            ->willReturn([]);

        $formDataRepositoryInterface
            ->getDataByEventIdAndUserId(777, 78)
            ->shouldBeCalled()
            ->willReturn([]);

        $formDataRepositoryInterface
            ->getDataByEventIdAndUserId(777, 34)
            ->shouldBeCalled()
            ->willReturn([]);

        $formDataRepositoryInterface
            ->getDataByEventIdAndUserId(777, 99)
            ->shouldBeCalled()
            ->willReturn([]);

        $userEventViewsFactory = new UserEventViewsFactory(
            $userEventViewRepository->reveal(),
            $extraDataRepository->reveal(),
            $queryBus->reveal(),
            $formDataRepositoryInterface->reveal()
        );
        $userEventViews = $userEventViewsFactory->getByEvent($event->reveal());

        $this->assertEquals($expectedUserEventViews, $userEventViews);
    }
}
