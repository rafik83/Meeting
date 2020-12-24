<?php

namespace Proximum\Vimeet\Tests\Application\Command\Template\Form;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\Command\Template\Form\ExportFormTemplateData\MailToAdmin;
use Proximum\Vimeet\Application\Command\Template\Form\ExportFormTemplateData\MailToAdminHandler;
use Proximum\Vimeet\Application\Command\Template\Form\ExportFormTemplateDataByUsers;
use Proximum\Vimeet\Application\Command\Template\Form\ExportFormTemplateDataByUsersHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Template\Form\ExportFormTemplateData\FormTemplateDataUserListViewQuery;
use Proximum\Vimeet\Application\View\Template\Form\ExportFormTemplateData\UserDataView;
use Proximum\Vimeet\Application\View\Template\Form\ExportFormTemplateData\UserListView;
use Proximum\Vimeet\Domain\File\FileFactory;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\User;

class ExportFormTemplateDataByUsersHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = $this->prophesize(Event::class);
        $event->getId()->shouldBeCalled()->willReturn(1);
        $formTemplate = $this->prophesize(FormTemplate::class);
        $formTemplate->getId()->shouldBeCalled()->willReturn(2);

        $admin = $this->prophesize(Admin::class);
        $user = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $users = [
            $user->reveal(),
            $user2->reveal(),
        ];

        $userListView = new UserListView(
            'fr',
            [
                new UserDataView(
                    1,
                    'nicolas@example.net',
                    'Nicolas',
                    'Example',
                    '+33123456789',
                    null,
                    11,
                    'Truc Muche',
                    'Exposant',
                    'Exposants',
                    '2 boulevard des trucs',
                    '75000',
                    'Paris',
                    'FR',
                    [
                        'key123' => 'Test',
                        'key1234' => 'Bidule',
                        'key12345' => 'Lorem > Ipsum',
                        'key123456' => '10/05/2018',
                    ]
                ),
                new UserDataView(
                    2,
                    'Pimprenelle@exampl.net',
                    'Pimprenelle',
                    'Foobar',
                    null,
                    '+33123456789',
                    12,
                    'Foo Bar',
                    'Visiteur',
                    null,
                    '15 avenue des bidules',
                    '75000',
                    'Paris',
                    'FR',
                    [
                        'key123' => 'ABC',
                        'key1234' => 'Machin',
                        'key12345' => 'Veni > Vidi > Vici',
                        'key123456' => '18/05/2018',
                    ]
                )
            ],
            [
                'key123' => 'Object 1',
                'key1234' => 'Object 2',
                'key12345' => 'Object 3',
                'key123456' => 'Object 4',
            ]
        );

        $serializer = $this->prophesize(SerializerAdapterInterface::class);
        $queryBus = $this->prophesize(QueryBusInterface::class);
        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $date = new \DateTime('2018-12-10 18:00:00.000');
        $fileFactory = $this->prophesize(FileFactory::class);
        $mailToAdminHandler = $this->prophesize(MailToAdminHandler::class);

        $queryBus->handle(new FormTemplateDataUserListViewQuery($event->reveal(), $formTemplate->reveal(), $users, 'fr'))
            ->shouldBeCalled()
            ->willReturn($userListView)
        ;
        $serializer->serialize($userListView, 'csv', ['csv_delimiter' => ';',])
            ->shouldBeCalled()
            ->willReturn('sheetId;sheetTitle;typeTitle;categoryTitle;userId;userFirstName;userLastName;userEmail;userPhone;userMobilePhone;sheetAddress;sheetZipCode;sheetCity;sheetCountry;key123;key1234;key12345;key123456
form_template_data_export.column.sheetId;form_template_data_export.column.sheetTitle;form_template_data_export.column.typeTitle;form_template_data_export.column.categoryTitle;form_template_data_export.column.userId;form_template_data_export.column.userFirstName;form_template_data_export.column.userLastName;form_template_data_export.column.userEmail;form_template_data_export.column.userPhone;form_template_data_export.column.userMobilePhone;form_template_data_export.column.sheetAddress;form_template_data_export.column.sheetZipCode;form_template_data_export.column.sheetCity;form_template_data_export.column.sheetCountry;"Quelque chose";"Autre chose";Nomenclature;"Date de creation"
11;"Truc Muche";Exposant;Exposants;1;Nicolas;Example;nicolas@example.net;\'+33123456789\';;"2 boulevard des trucs";75000;Paris;FR;Test;Bidule;"Lorem > Ipsum";10/05/2018
12;"Foo Bar";Visiteur;;2;Pimprenelle;Foobar;Pimprenelle@example.net;;\'+33123456789\';"15 avenue des bidules";75000;Paris;FR;ABC;Machin;"Veni > Vidi > Vici";18/05/2018
'
            )
        ;

        $fileStorage->create(
                'form_template_data_export.column.sheetId;form_template_data_export.column.sheetTitle;form_template_data_export.column.typeTitle;form_template_data_export.column.categoryTitle;form_template_data_export.column.userId;form_template_data_export.column.userFirstName;form_template_data_export.column.userLastName;form_template_data_export.column.userEmail;form_template_data_export.column.userPhone;form_template_data_export.column.userMobilePhone;form_template_data_export.column.sheetAddress;form_template_data_export.column.sheetZipCode;form_template_data_export.column.sheetCity;form_template_data_export.column.sheetCountry;"Quelque chose";"Autre chose";Nomenclature;"Date de creation"
11;"Truc Muche";Exposant;Exposants;1;Nicolas;Example;nicolas@example.net;\'+33123456789\';;"2 boulevard des trucs";75000;Paris;FR;Test;Bidule;"Lorem > Ipsum";10/05/2018
12;"Foo Bar";Visiteur;;2;Pimprenelle;Foobar;Pimprenelle@example.net;;\'+33123456789\';"15 avenue des bidules";75000;Paris;FR;ABC;Machin;"Veni > Vidi > Vici";18/05/2018
',
                'export_form_template_1_2_18_00_00_10_12_2018.csv',
                '/path/to/export/file'
            )
            ->shouldBeCalled()
            ->willReturn('/path/to/export/file/export_form_template_1_2_18_00_00_10_12_2018.csv')
        ;

        $file = $this->prophesize(File::class);
        $fileFactory
            ->createAndPersistFile(
                '/path/to/export/file/export_form_template_1_2_18_00_00_10_12_2018.csv',
                File::TYPE_EXPORT_FORM_TEMPLATE_DATA
            )->shouldBeCalled()
            ->willReturn($file->reveal())
        ;

        $mailToAdminHandler->handle(new MailToAdmin($admin->reveal(), $file->reveal(), $event->reveal(), 'fr'))
            ->shouldBeCalled()
        ;


        $handler = new ExportFormTemplateDataByUsersHandler(
            $serializer->reveal(),
            $queryBus->reveal(),
            $fileStorage->reveal(),
            '/path/to/export/file',
            $fileFactory->reveal(),
            $date,
            $mailToAdminHandler->reveal()
        );

        $handler->handle(new ExportFormTemplateDataByUsers(
            $event->reveal(),
            $formTemplate->reveal(),
            $users,
            $admin->reveal(),
            'fr'
        ));
    }
}
