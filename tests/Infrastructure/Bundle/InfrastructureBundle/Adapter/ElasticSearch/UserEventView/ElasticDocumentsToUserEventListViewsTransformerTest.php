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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\UserEventView\UserEventListView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\ElasticSearch\UserEventView\ElasticDocumentsToUserEventListViewsTransformer;

class ElasticDocumentsToUserEventListViewsTransformerTest extends TestCase
{
    public function testHandle()
    {
        $sheet1337 = $this->prophesize(Sheet::class);
        $sheet4556 = $this->prophesize(Sheet::class);

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
                [$sheet1337->reveal()]
            ),
            new UserEventListView(
                42,
                2,
                'Leeloo',
                'Ekbat de Sebat',
                'leeloo@fifth.element',
                'fr',
                [
                    $sheet1337->reveal(),
                    $sheet4556->reveal(),
                ]
            ),
        ];

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository
            ->getSheetsByIdsWithTypesAndCategories([1337, 4556], 'fr')
            ->shouldBeCalled()
            ->willReturn([1337 => $sheet1337->reveal(), 4556 => $sheet4556->reveal()])
        ;

        $elasticDocumentsToUserEventListViewsTranformer = new ElasticDocumentsToUserEventListViewsTransformer(
            $sheetRepository->reveal()
        );
        $this->assertEquals($expectedResult, $elasticDocumentsToUserEventListViewsTranformer->handle($documents, 'fr'));
    }
}
