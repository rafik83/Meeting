<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Rooming\Export;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Command\Rooming\Export\ExportRoomingList;
use Proximum\Vimeet\Application\Command\Rooming\Export\ExportRoomingListHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Rooming\Export\MailToAdmin;
use Proximum\Vimeet\Application\Command\Rooming\Export\MailToAdminHandler;
use Proximum\Vimeet\Application\Query\Rooming\RoomingList\Export\RoomingListViewQuery;
use Proximum\Vimeet\Application\View\Rooming\ExportList\RoomingListView;
use Proximum\Vimeet\Domain\File\FileFactory;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;

class ExportRoomingListHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $admin = $this->prophesize(Admin::class);

        $roomingListView = $this->prophesize(RoomingListView::class);
        $csv = '';
        $csvWithoutKey = '';
        $event->getId()->shouldBeCalled()->willReturn(1);

        $queryBus = $this->prophesize(QueryBusInterface::class);
        $serializer = $this->prophesize(SerializerAdapterInterface::class);
        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileFactory = $this->prophesize(FileFactory::class);
        $mailToAdminHandler = $this->prophesize(MailToAdminHandler::class);
        $file = $this->prophesize(File::class);
        $dateTime = new \DateTime('2019-01-10 10:00:00.000');

        $queryBus->handle(new RoomingListViewQuery($event->reveal(), 'fr'))->shouldBeCalled()->willReturn($roomingListView->reveal());
        $serializer->serialize($roomingListView, 'csv', ['csv_delimiter' => ';'])->shouldBeCalled()->willReturn($csv);
        $fileStorage
            ->create(
                $csvWithoutKey,
                'export_rooming_list_1_10_00_00_10_01_2019.csv',
                'path/to/store/export'
            )
            ->shouldBeCalled()
            ->willReturn('path/to/store/export/export_rooming_list_1_10_00_00_10_01_2019.csv')
        ;

        $fileFactory
            ->createAndPersistFile('path/to/store/export/export_rooming_list_1_10_00_00_10_01_2019.csv', File::TYPE_EXPORT_ROOMING_LIST)
            ->shouldBeCalled()
            ->willReturn($file->reveal())
        ;

        $mailToAdminHandler
            ->handle(new MailToAdmin(
                $event->reveal(),
                $admin->reveal(),
                $file->reveal(),
                'fr'
            ))
            ->shouldBeCalled()
        ;

        $handler = new ExportRoomingListHandler(
            $queryBus->reveal(),
            $serializer->reveal(),
            $fileStorage->reveal(),
            $fileFactory->reveal(),
            'path/to/store/export',
            $mailToAdminHandler->reveal(),
            $dateTime
        );

        $handler->handle(new ExportRoomingList($event->reveal(), $admin->reveal(), 'fr'));
    }
}
