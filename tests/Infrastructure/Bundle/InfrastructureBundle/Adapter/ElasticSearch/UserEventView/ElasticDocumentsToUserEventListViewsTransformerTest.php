<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;
use Proximum\Vimeet\Domain\UserEventView\UserEventListView;
use Proximum\Vimeet\Domain\UserEventView\UserEventSheetsListView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView\ElasticDocumentsToUserEventListViewsTransformer;

class ElasticDocumentsToUserEventListViewsTransformerTest extends TestCase
{
    public function testHandle()
    {
        $extraData = $this->prophesize(ExtraData::class);
        $extraData->getValue()->willReturn('true');

        $participant = $this->prophesize(Participant::class);
        $participant->isVisio()->willReturn(true);
        $participant2 = $this->prophesize(Participant::class);
        $participant2->isVisio()->willReturn(false);

        $sheet1337 = $this->prophesize(Sheet::class);
        $sheet1337->getId()->willReturn(1337);
        $sheet1337->getTitle()->willReturn('Taxi Company');
        $sheet1337->getOwnerId()->willReturn(1);
        $sheet1337->getTypeTitle('fr')->willReturn('Taxi drivers');
        $sheet1337->getCategoriesTitles('fr')->willReturn('Drivers');
        $sheet1337->isEnabled()->willReturn(false);
        $sheet1337->getState()->willReturn('pending');
        $sheet1337->getValidationState()->willReturn('validated');
        $sheet1337->getCompleteness()->willReturn(100);
        $sheet1337->attend()->willReturn(false);
        $sheet1337->hasGroup()->willReturn(true);
        $sheet1337->getGroupTitle()->willReturn('My sheets group');
        $sheet1337->isInInternalCatalog()->willReturn(false);
        $sheet1337->getFollowerName()->willReturn(null);
        $sheet1337->getCommercialStatus()->willReturn('verbal_agreement');
        $sheet1337->getCommercialStatusLabel()->willReturn('success');
        $sheet1337->getParticipantByUserId(1)->willReturn($participant);
        $sheet1337->getParticipantByUserId(2)->willReturn($participant2);

        $sheet4556 = $this->prophesize(Sheet::class);
        $sheet4556->getId()->willReturn(4556);
        $sheet4556->getTitle()->willReturn('Fhloston paradise');
        $sheet4556->getOwnerId()->willReturn(2);
        $sheet4556->getTypeTitle('fr')->willReturn('Cruise');
        $sheet4556->getCategoriesTitles('fr')->willReturn('Boat');
        $sheet4556->isEnabled()->willReturn(true);
        $sheet4556->getState()->willReturn('pending');
        $sheet4556->getValidationState()->willReturn('validated');
        $sheet4556->getCompleteness()->willReturn(100);
        $sheet4556->attend()->willReturn(true);
        $sheet4556->hasGroup()->willReturn(false);
        $sheet4556->getGroupTitle()->willReturn(null);
        $sheet4556->isInInternalCatalog()->willReturn(true);
        $sheet4556->getFollowerName()->willReturn('Henry MICHOU');
        $sheet4556->getCommercialStatus()->willReturn('verbal_agreement');
        $sheet4556->getCommercialStatusLabel()->willReturn('success');
        $sheet4556->getParticipantByUserId(2)->willReturn($participant2);

        $documents = [
            new \Elastica\Document(
                '42_1',
                [
                    'eventId' => 42,
                    'userId' => 1,
                    'firstName' => 'Korben',
                    'lastName' => 'DALLAS',
                    'email' => 'korben.dallas@fifth.element',
                    'locale' => 'en',
                    'sheets' => [
                        ['id' => 1337]
                    ],
                ]
            ),
            new \Elastica\Document(
                '42_2',
                [
                    'eventId' => 42,
                    'userId' => 2,
                    'firstName' => 'Leeloo',
                    'lastName' => 'Ekbat de Sebat',
                    'email' => 'leeloo@fifth.element',
                    'locale' => 'fr',
                    'sheets' => [
                        ['id' => 1337],
                        ['id' => 4556],
                    ],
                ]
            ),
        ];

        $expectedResult = [
            new UserEventListView(
                42,
                1,
                'Korben',
                'DALLAS',
                'korben.dallas@fifth.element',
                'en',
                [
                    new UserEventSheetsListView(
                        1337,
                        'Taxi Company',
                        true,
                        'Taxi drivers',
                        'Drivers',
                        false,
                        'pending',
                        'validated',
                        100,
                        'success',
                        false,
                        true,
                        'My sheets group',
                        false,
                        null,
                        'verbal_agreement',
                        'success',
                        true,
                        true
                    ),
                ]
            ),
            new UserEventListView(
                42,
                2,
                'Leeloo',
                'Ekbat de Sebat',
                'leeloo@fifth.element',
                'fr',
                [
                    new UserEventSheetsListView(
                        1337,
                        'Taxi Company',
                        false,
                        'Taxi drivers',
                        'Drivers',
                        false,
                        'pending',
                        'validated',
                        100,
                        'success',
                        false,
                        true,
                        'My sheets group',
                        false,
                        null,
                        'verbal_agreement',
                        'success',
                        false,
                        false
                    ),
                    new UserEventSheetsListView(
                        4556,
                        'Fhloston paradise',
                        true,
                        'Cruise',
                        'Boat',
                        true,
                        'pending',
                        'validated',
                        100,
                        'success',
                        true,
                        false,
                        null,
                        true,
                        'Henry MICHOU',
                        'verbal_agreement',
                        'success',
                        false,
                        false
                    ),
                ]
            ),
        ];

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository
            ->getSheetsByIdsWithTypesAndCategories([1337, 4556], 'fr')
            ->shouldBeCalled()
            ->willReturn([1337 => $sheet1337->reveal(), 4556 => $sheet4556->reveal()])
        ;

        $extraDataRepository->getExtraDataForEventIdNameAndUserId(42, Type::VISIO_TESTED, 1)
            ->shouldBeCalled()
            ->willReturn($extraData);

        $extraDataRepository->getExtraDataForEventIdNameAndUserId(42, Type::VISIO_TESTED, 2)
            ->shouldBeCalled()
            ->willReturn(null);

        $elasticDocumentsToUserEventListViewsTranformer = new ElasticDocumentsToUserEventListViewsTransformer(
            $sheetRepository->reveal(),
            $extraDataRepository->reveal()
        );
        $this->assertEquals($expectedResult, $elasticDocumentsToUserEventListViewsTranformer->handle($documents, 'fr'));
    }
}
