<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Get\Query;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Query\FieldsByEventQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Query\FieldsByEventQueryHandler;

class FieldsByEventQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $typesMapping = [
            '1337' => [
                'CategorieIndividuEvt' => 'VISITEUR',
                'ZL_PROFIL' => 'SALON',
            ],
            '9966' => [
                'CategorieIndividuEvt' => 'VISITEUR',
                'ZL_PROFIL' => 'PROSPECT',
            ],
            '1324' => [
                'ZL_SOUSCATEGORIE' => 1324,
            ],
            '879' => [
                'CategorieIndividuEvt' => 'TYPE-NOT-EXISTS',
            ],
        ];

        $customDataMapping = [
            'tags' => [
                'sheet_activity' => 'ZL_PROFIL',
                'sheet_organization_staff' => 'ZL_Effectif',
                'sheet_generic_tag_20' => 'ZL_ACTIVITE',
                'sheet_template_generic_tag_10' => 'ZL_TypePrestation',
            ],
        ];

        $expectedResult = [
            'Id',
            'CategorieIndividuEvt',
            'Societe',
            'Civilite',
            'Prenom',
            'Nom',
            'EvenementFonction',
            'Email',
            'TelephoneFixe',
            'Mobile',
            'TelephoneMobile',
            'Adresse1',
            'CodePostal',
            'Ville',
            'Pays',
            'Langue',
            'CreeLe',
            'ModifieLe',
            'ZL_PROFIL',
            'ZL_SOUSCATEGORIE',
            'ZL_Effectif',
            'ZL_ACTIVITE',
            'ZL_TypePrestation',
        ];

        $fieldsByEventQueryHandler = new FieldsByEventQueryHandler();

        $result = $fieldsByEventQueryHandler->handle(new FieldsByEventQuery($typesMapping, $customDataMapping));

        $this->assertEquals($expectedResult, $result);
    }
}
