<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Get\Query;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Query\FieldsByEventQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Get\Query\FieldsByEventQueryHandler;

class FieldsByEventQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $mapping = [
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

        $expectedResult = [
            'Id',
            'CategorieIndividuEvt',
            'Societe',
            'ZL_SOUSCATEGORIE',
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
        ];

        $fieldsByEventQueryHandler = new FieldsByEventQueryHandler();

        $result = $fieldsByEventQueryHandler->handle(new FieldsByEventQuery($mapping));

        $this->assertEquals($expectedResult, $result);
    }
}
