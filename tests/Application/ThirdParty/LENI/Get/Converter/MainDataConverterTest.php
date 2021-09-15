<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Get\Converter;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Converter\MainDataConverter;

class MainDataConverterTest extends TestCase
{
    public function testConvert()
    {
        $data = [
            'Email' => 'bruce@willis.us',
            'Societe' => 'World Company',
            'Civilite' => 'MLLE',
            'Nom' => 'Willis',
            'Prenom' => 'Bruce',
            'Adresse1' => '16 rue Hollywood',
            'CodePostal' => '888999',
            'Ville' => 'Los Angeles',
            'TelephoneFixe' => '+166778899',
            'TelephoneMobile' => '+69988776655',
            'Pays' => 'FR',
            'EvenementFonction' => 'F06'
        ];
        $expectedResult = [
            'sheet_title' => 'World Company',
            'sheet_organization' => 'World Company',
            'sheet_address' => '16 rue Hollywood',
            'sheet_zipcode' => '888999',
            'sheet_city' => 'Los Angeles',
            'sheet_country' => 'FR',
            'participant_gender' => 'woman',
            'participant_firstname' => 'Bruce',
            'participant_lastname' => 'Willis',
            'participant_mobile' => '+69988776655',
            'participant_phone' => '+166778899',
            'participant_address' => '16 rue Hollywood',
            'participant_zipcode' => '888999',
            'participant_city' => 'Los Angeles',
            'participant_country' => 'FR',
            'participant_position' => 'F06',
        ];

        $mainDataConverter = new MainDataConverter();
        $result = $mainDataConverter->convert($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function testConvertWithNoCompanyName()
    {
        $data = [
            'Email' => 'bruce@willis.us',
            'Societe' => '',
            'Nom' => 'Willis',
            'Prenom' => 'Bruce',
        ];
        $expectedResult = [
            'sheet_title' => 'Willis Bruce',
            'sheet_organization' => '',
            'sheet_address' => null,
            'sheet_zipcode' => null,
            'sheet_city' => null,
            'sheet_country' => null,
            'participant_gender' => null,
            'participant_firstname' => 'Bruce',
            'participant_lastname' => 'Willis',
            'participant_mobile' => null,
            'participant_phone' => null,
            'participant_address' => null,
            'participant_zipcode' => null,
            'participant_city' => null,
            'participant_country' => null,
            'participant_position' => null,
        ];

        $mainDataConverter = new MainDataConverter();
        $result = $mainDataConverter->convert($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function testConvertWithEmailAsSheetTitle()
    {
        $data = [
            'Email' => 'bruce@willis.us',
        ];
        $expectedResult = [
            'sheet_title' => 'bruce@willis.us',
            'sheet_organization' => null,
            'sheet_address' => null,
            'sheet_zipcode' => null,
            'sheet_city' => null,
            'sheet_country' => null,
            'participant_gender' => null,
            'participant_firstname' => null,
            'participant_lastname' => null,
            'participant_mobile' => null,
            'participant_phone' => null,
            'participant_address' => null,
            'participant_zipcode' => null,
            'participant_city' => null,
            'participant_country' => null,
            'participant_position' => null,
        ];

        $mainDataConverter = new MainDataConverter();
        $result = $mainDataConverter->convert($data);

        $this->assertEquals($expectedResult, $result);
    }
}
