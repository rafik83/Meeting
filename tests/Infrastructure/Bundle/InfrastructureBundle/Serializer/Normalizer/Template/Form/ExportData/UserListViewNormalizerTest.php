<?php

namespace Proximum\Vimeet\Tests\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Template\Form\ExportData;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\View\Template\Form\ExportFormTemplateData\UserDataView;
use Proximum\Vimeet\Application\View\Template\Form\ExportFormTemplateData\UserListView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Template\Form\ExportData\UserListViewNormalizer;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\TranslatorInterface;

class UserListViewNormalizerTest extends TestCase
{
    public function testNormalization(): void
    {
        $translator = $this->prophesize(TranslatorInterface::class);

        $translator
            ->trans('form_template_data_export.column.sheetId', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('sheetId')
        ;
        $translator
            ->trans('form_template_data_export.column.sheetTitle', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('sheetTitle')
        ;
        $translator
            ->trans('form_template_data_export.column.typeTitle', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('typeTitle')
        ;
        $translator
            ->trans('form_template_data_export.column.categoryTitle', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('categoryTitle')
        ;
        $translator
            ->trans('form_template_data_export.column.userId', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('userId')
        ;
        $translator
            ->trans('form_template_data_export.column.userFirstName', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('userFirstName')
        ;
        $translator
            ->trans('form_template_data_export.column.userLastName', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('userLastName')
        ;
        $translator
            ->trans('form_template_data_export.column.userEmail', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('userEmail')
        ;
        $translator
            ->trans('form_template_data_export.column.userPhone', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('userPhone')
        ;
        $translator
            ->trans('form_template_data_export.column.userMobilePhone', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('userMobilePhone')
        ;
        $translator
            ->trans('form_template_data_export.column.sheetAddress', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('sheetAddress')
        ;
        $translator
            ->trans('form_template_data_export.column.sheetZipCode', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('sheetZipCode')
        ;
        $translator
            ->trans('form_template_data_export.column.sheetCity', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('sheetCity')
        ;
        $translator
            ->trans('form_template_data_export.column.sheetCountry', [], 'export', 'fr')
            ->shouldBeCalled()
            ->willReturn('sheetCountry')
        ;

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
                'key123' => 'Quelque chose',
                'key1234' => 'Autre chose',
                'key12345' => 'Nomenclature',
                'key123456' => 'Date de creation',
            ]
        );

        $normalizer = new UserListViewNormalizer($translator->reveal());
        $result = $normalizer->normalize($userListView, 'csv', ['csv_delimiter' => ';']);

        $expected = [
            [
                'sheetId' => 'sheetId',
                'sheetTitle' => 'sheetTitle',
                'typeTitle' => 'typeTitle',
                'categoryTitle' => 'categoryTitle',
                'userId' => 'userId',
                'userFirstName' => 'userFirstName',
                'userLastName' => 'userLastName',
                'userEmail' => 'userEmail',
                'userPhone' => 'userPhone',
                'userMobilePhone' => 'userMobilePhone',
                'sheetAddress' => 'sheetAddress',
                'sheetZipCode' => 'sheetZipCode',
                'sheetCity' => 'sheetCity',
                'sheetCountry' => 'sheetCountry',
                'key123' => 'Quelque chose',
                'key1234' => 'Autre chose',
                'key12345' => 'Nomenclature',
                'key123456' => 'Date de creation',
            ],
            [
                'sheetId' => 11,
                'sheetTitle' => 'Truc Muche',
                'typeTitle' => 'Exposant',
                'categoryTitle' => 'Exposants',
                'userId' => 1,
                'userFirstName' => 'Nicolas',
                'userLastName' => 'Example',
                'userEmail' => 'nicolas@example.net',
                'userPhone' => "'+33123456789'",
                'userMobilePhone' => null,
                'sheetAddress' => '2 boulevard des trucs',
                'sheetZipCode' => '75000',
                'sheetCity' => 'Paris',
                'sheetCountry' => 'FR',
                'key123' => 'Test',
                'key1234' => 'Bidule',
                'key12345' => 'Lorem > Ipsum',
                'key123456' => '10/05/2018',
            ],
            [
                'sheetId' => 12,
                'sheetTitle' => 'Foo Bar',
                'typeTitle' => 'Visiteur',
                'categoryTitle' => null,
                'userId' => 2,
                'userFirstName' => 'Pimprenelle',
                'userLastName' => 'Foobar',
                'userEmail' => 'Pimprenelle@exampl.net',
                'userPhone' => null,
                'userMobilePhone' => "'+33123456789'",
                'sheetAddress' => '15 avenue des bidules',
                'sheetZipCode' => '75000',
                'sheetCity' => 'Paris',
                'sheetCountry' => 'FR',
                'key123' => 'ABC',
                'key1234' => 'Machin',
                'key12345' => 'Veni > Vidi > Vici',
                'key123456' => '18/05/2018',
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    public function testSerialize()
    {
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
                    'Pimprenelle@example.net',
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
                'key123' => 'Quelque chose',
                'key1234' => 'Autre chose',
                'key12345' => 'Nomenclature',
                'key123456' => 'Date de creation',
            ]
        );

        $translator = new Translator('fr');
        $serializer = new Serializer(
            [
                new UserListViewNormalizer($translator),
                new ObjectNormalizer(),
            ],
            [
                new CsvEncoder(),
            ]
        );

        $result = $serializer->serialize($userListView, 'csv', ['csv_delimiter' => ';']);

        $expected = 'sheetId;sheetTitle;typeTitle;categoryTitle;userId;userFirstName;userLastName;userEmail;userPhone;userMobilePhone;sheetAddress;sheetZipCode;sheetCity;sheetCountry;key123;key1234;key12345;key123456
form_template_data_export.column.sheetId;form_template_data_export.column.sheetTitle;form_template_data_export.column.typeTitle;form_template_data_export.column.categoryTitle;form_template_data_export.column.userId;form_template_data_export.column.userFirstName;form_template_data_export.column.userLastName;form_template_data_export.column.userEmail;form_template_data_export.column.userPhone;form_template_data_export.column.userMobilePhone;form_template_data_export.column.sheetAddress;form_template_data_export.column.sheetZipCode;form_template_data_export.column.sheetCity;form_template_data_export.column.sheetCountry;"Quelque chose";"Autre chose";Nomenclature;"Date de creation"
11;"Truc Muche";Exposant;Exposants;1;Nicolas;Example;nicolas@example.net;\'+33123456789\';;"2 boulevard des trucs";75000;Paris;FR;Test;Bidule;"Lorem > Ipsum";10/05/2018
12;"Foo Bar";Visiteur;;2;Pimprenelle;Foobar;Pimprenelle@example.net;;\'+33123456789\';"15 avenue des bidules";75000;Paris;FR;ABC;Machin;"Veni > Vidi > Vici";18/05/2018
';

        $this->assertEquals($expected, $result);
    }
}
