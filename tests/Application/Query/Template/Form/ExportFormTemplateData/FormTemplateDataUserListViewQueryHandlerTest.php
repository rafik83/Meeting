<?php

namespace Proximum\Vimeet\Tests\Application\Query\Template\Form\ExportFormTemplateData;

use Proximum\Vimeet\Application\Query\Template\Form\ExportFormTemplateData\FormTemplateDataForUser;
use Proximum\Vimeet\Application\Query\Template\Form\ExportFormTemplateData\FormTemplateDataForUserHandler;
use Proximum\Vimeet\Application\Query\Template\Form\ExportFormTemplateData\FormTemplateDataUserListViewQuery;
use Proximum\Vimeet\Application\Query\Template\Form\ExportFormTemplateData\FormTemplateDataUserListViewQueryHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\View\Template\Form\ExportFormTemplateData\UserDataView;
use Proximum\Vimeet\Application\View\Template\Form\ExportFormTemplateData\UserListView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Proximum\Vimeet\Domain\Template\TemplateObject\EditableText;
use Proximum\Vimeet\Domain\Template\TemplateObject\Nomenclature;

class FormTemplateDataUserListViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        // Init
        $event = $this->prophesize(Event::class);
        $formTemplate = $this->prophesize(FormTemplate::class);
        $user1 = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);

        $event->getAvailableLocale('fr')->shouldBeCalled()->willReturn('fr');
        $event->getFallback()->shouldBeCalled()->willReturn('en');

        $userDataView1 = new UserDataView(
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
        );
        $userDataView2 = new UserDataView(
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
        );

        // Services
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $formTemplateDataForUserHandler = $this->prophesize(FormTemplateDataForUserHandler::class);

        $formTemplateDataForUserHandler
            ->handle(new FormTemplateDataForUser($event->reveal(), $user1->reveal(), $formTemplate->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($userDataView1)
        ;

        $formTemplateDataForUserHandler
            ->handle(new FormTemplateDataForUser($event->reveal(), $user2->reveal(), $formTemplate->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($userDataView2)
        ;

        $templateData = $this->prophesize(TemplateData::class);
        $templateDataFactory->createFormTemplateFromTemplate($formTemplate->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($templateData->reveal())
        ;

        $object1 = $this->prophesize(EditableText::class);
        $object2 = $this->prophesize(EditableText::class);
        $object3 = $this->prophesize(Nomenclature::class);
        $object4 = $this->prophesize(EditableText::class);

        $object1->getKey()->shouldBeCalled()->willReturn('key123');
        $object2->getKey()->shouldBeCalled()->willReturn('key1234');
        $object3->getKey()->shouldBeCalled()->willReturn('key12345');
        $object4->getKey()->shouldBeCalled()->willReturn('key123456');

        $object1->getExportableFieldname('fr', 'en')->shouldBeCalled()->willReturn('Object 1');
        $object2->getExportableFieldname('fr', 'en')->shouldBeCalled()->willReturn('Object 2');
        $object3->getExportableFieldname('fr', 'en')->shouldBeCalled()->willReturn('Object 3');
        $object4->getExportableFieldname('fr', 'en')->shouldBeCalled()->willReturn('Object 4');

        $templateData
            ->getExportableObjects()
            ->shouldBeCalled()
            ->willReturn([
                $object1,
                $object2,
                $object3,
                $object4,
            ])
        ;

        $query = new FormTemplateDataUserListViewQuery(
            $event->reveal(),
            $formTemplate->reveal(),
            [
                $user1->reveal(),
                $user2->reveal(),
            ],
            'fr'
        );
        $handler = new FormTemplateDataUserListViewQueryHandler(
            $templateDataFactory->reveal(),
            $formTemplateDataForUserHandler->reveal()
        );

        $result = $handler->handle($query);

        $expected = new UserListView(
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

        $this->assertEquals($expected, $result);
    }
}
