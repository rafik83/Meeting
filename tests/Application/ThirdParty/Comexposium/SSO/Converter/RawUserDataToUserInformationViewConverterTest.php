<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\Comexposium\SSO\Converter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\Converter\RawUserDataToUserInformationViewConverter;
use Proximum\Vimeet\Application\ThirdParty\Comexposium\SSO\View\UserInformationView;

class RawUserDataToUserInformationViewConverterTest extends TestCase
{
    public function testConvert()
    {
        $rawUserDataToUserInformationViewConverter = new RawUserDataToUserInformationViewConverter();
        $userInformationView = $rawUserDataToUserInformationViewConverter->convert(
            'myemail@example.com',
            'fr',
            [
                'civility' => '0',
                'firstname' => 'Bruce',
                'lastname' => 'Willis',
                'mobilephone:indicatif' => '+33',
                'mobilephone:content' => '699887766',
                'country' => 'FRA',
            ]
        );

        $expectedUserInformationView = new UserInformationView(
            'myemail@example.com',
            'man',
            'Bruce',
            'Willis',
            '+33699887766',
            'FR',
            'fr'
        );

        $this->assertEquals($expectedUserInformationView, $userInformationView);
    }

    public function testConvertWithNull()
    {
        $rawUserDataToUserInformationViewConverter = new RawUserDataToUserInformationViewConverter();
        $userInformationView = $rawUserDataToUserInformationViewConverter->convert(
            'myemail@example.com',
            'fr',
            [
                'civility' => '2',
                'firstname' => 'Bruce',
                'lastname' => 'Willis',
            ]
        );

        $expectedUserInformationView = new UserInformationView(
            'myemail@example.com',
            null,
            'Bruce',
            'Willis',
            null,
            null,
            'fr'
        );

        $this->assertEquals($expectedUserInformationView, $userInformationView);
    }
}
