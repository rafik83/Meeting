<?php

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
        $csv = 'sheetId;sheetTitle;typeTitle;spotReference;userId;userGender;userFirstName;userLastName;userComment;userTesting;accommodationTitle;roomType;arrival;departure;roomNumber;roommate.sheetId;roommate.sheetTitle;roommate.typeTitle;roommate.spotReference;roommate.userId;roommate.userGender;roommate.userFirstName;roommate.userLastName;roommate.userComment;roommate.userTesting
rooming_list_data_export.column.sheetId;rooming_list_data_export.column.sheetTitle;rooming_list_data_export.column.typeTitle;rooming_list_data_export.column.spotReference;rooming_list_data_export.column.userId;rooming_list_data_export.column.userGender;rooming_list_data_export.column.userFirstName;rooming_list_data_export.column.userLastName;rooming_list_data_export.column.userComment;rooming_list_data_export.column.userTesting;rooming_list_data_export.column.accommodationTitle;rooming_list_data_export.column.roomType;rooming_list_data_export.column.arrival;rooming_list_data_export.column.departure;rooming_list_data_export.column.roomNumber;rooming_list_data_export.column.roommate.sheetId;rooming_list_data_export.column.roommate.sheetTitle;rooming_list_data_export.column.roommate.typeTitle;rooming_list_data_export.column.roommate.spotReference;rooming_list_data_export.column.roommate.userId;rooming_list_data_export.column.roommate.userGender;rooming_list_data_export.column.roommate.userFirstName;rooming_list_data_export.column.roommate.userLastName;rooming_list_data_export.column.roommate.userComment;rooming_list_data_export.column.roommate.userTesting
1,2,3;Aanera,Bbnera,Ccnera;Exposant,Visiteur;A123;1;gender.gender.man;Jean;Paul;"This is a comment";"This is a testing";Mariott;rooming_list_data_export.column.roomType.single;08/01/2019;10/01/2019;A123;;;;;;;;;;
1;Aanera;Exposant;;2;gender.gender.woman;Marie;Curie;"No comment";;Mariott;rooming_list_data_export.column.roomType.single;08/01/2019;12/01/2019;A124;;;;;;;;;;
4,5;Lorem,Ipsum;Exposant;A321;3;gender.gender.man;Jean;Paul;;;Mariott;rooming_list_data_export.column.roomType.double;08/01/2019;12/01/2019;A125;5;Aanera,Bbnera,Ccnera;Exposant,Visiteur;;4;;Bidule;Truc;"A comment";"A testing info"
1,2,3;Aanera,Bbnera,Ccnera;Exposant,Visiteur;A123;1;gender.gender.man;Jean;Paul;"This is a comment";"This is a testing";Novotel;rooming_list_data_export.column.roomType.single;10/01/2019;12/01/2019;A126;;;;;;;;;;
';
        $csvWithoutKey = 'rooming_list_data_export.column.sheetId;rooming_list_data_export.column.sheetTitle;rooming_list_data_export.column.typeTitle;rooming_list_data_export.column.spotReference;rooming_list_data_export.column.userId;rooming_list_data_export.column.userGender;rooming_list_data_export.column.userFirstName;rooming_list_data_export.column.userLastName;rooming_list_data_export.column.userComment;rooming_list_data_export.column.userTesting;rooming_list_data_export.column.accommodationTitle;rooming_list_data_export.column.roomType;rooming_list_data_export.column.arrival;rooming_list_data_export.column.departure;rooming_list_data_export.column.roomNumber;rooming_list_data_export.column.roommate.sheetId;rooming_list_data_export.column.roommate.sheetTitle;rooming_list_data_export.column.roommate.typeTitle;rooming_list_data_export.column.roommate.spotReference;rooming_list_data_export.column.roommate.userId;rooming_list_data_export.column.roommate.userGender;rooming_list_data_export.column.roommate.userFirstName;rooming_list_data_export.column.roommate.userLastName;rooming_list_data_export.column.roommate.userComment;rooming_list_data_export.column.roommate.userTesting
1,2,3;Aanera,Bbnera,Ccnera;Exposant,Visiteur;A123;1;gender.gender.man;Jean;Paul;"This is a comment";"This is a testing";Mariott;rooming_list_data_export.column.roomType.single;08/01/2019;10/01/2019;A123;;;;;;;;;;
1;Aanera;Exposant;;2;gender.gender.woman;Marie;Curie;"No comment";;Mariott;rooming_list_data_export.column.roomType.single;08/01/2019;12/01/2019;A124;;;;;;;;;;
4,5;Lorem,Ipsum;Exposant;A321;3;gender.gender.man;Jean;Paul;;;Mariott;rooming_list_data_export.column.roomType.double;08/01/2019;12/01/2019;A125;5;Aanera,Bbnera,Ccnera;Exposant,Visiteur;;4;;Bidule;Truc;"A comment";"A testing info"
1,2,3;Aanera,Bbnera,Ccnera;Exposant,Visiteur;A123;1;gender.gender.man;Jean;Paul;"This is a comment";"This is a testing";Novotel;rooming_list_data_export.column.roomType.single;10/01/2019;12/01/2019;A126;;;;;;;;;;
';
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
