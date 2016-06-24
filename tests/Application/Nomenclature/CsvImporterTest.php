<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Nomenclature;

use Proximum\Vimeet\Application\Nomenclature\CsvImporter;
use Proximum\Vimeet\Application\Nomenclature\Id\IdGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Nomenclature;

class CsvImporterTest extends \PHPUnit_Framework_TestCase
{
    private static $index = 0;

    public function testImport()
    {
        $ids = [
            'aaaaaa', 'aaaaab', 'aaaaac', 'aaaaad', 'aaaaae', 'aaaaaf', 'aaaaag', 'aaaaah', 'aaaaai', 'aaaaaj', 'aaaaak', 'aaaaal', 'aaaaam', 'aaaaan', 'aaaaao', 'aaaaap', 'aaaaaq', 'aaaaar', 'aaaaas', 'aaaaat', 'aaaaau', 'aaaaav', 'aaaaaw', 'aaaaax', 'aaaaay', 'aaaaaz',
            'aaaaba', 'aaaabb', 'aaaabc', 'aaaabd', 'aaaabe', 'aaaabf', 'aaaabg', 'aaaabh', 'aaaabi', 'aaaabj', 'aaaabk', 'aaaabl', 'aaaabm', 'aaaabn', 'aaaabo', 'aaaabp', 'aaaabq', 'aaaabr', 'aaaabs', 'aaaabt', 'aaaabu', 'aaaabv', 'aaaabw', 'aaaabx', 'aaaaby', 'aaaabz',
        ];

        CsvImporterTest::$index = 0;

        $generator = $this->prophesize(IdGeneratorInterface::class);
        $generator->generate()->shouldBeCalled()->will(function () use ($ids) {
            return $ids[CsvImporterTest::$index++];
        });

        $importer     = new CsvImporter($generator->reveal());
        $nomenclature = new Nomenclature('Offres et besoins', 1, [
            'aaaaaa' => ['label' => [
                'fr' => 'Brevets',
                'en' => 'Brevets en']
            ],
            'aaaaab' => ['label' => [
                'fr' => 'conception',
                'en' => 'conception en']
            ],
            'aaaaac' => ['label' => [
                'fr' => 'conseil et/ou services',
                'en' => 'conseil et/ou services en']
            ],
            'aaaaad' => ['label' => [
                'fr' => 'Design',
                'en' => 'Design en']
            ],
            'aaaaae' => ['label' => [
                'fr' => 'Etude et ingénierie',
                'en' => 'Etude et ingénierie en']
            ],
            'aaaaaf' => ['label' => [
                'fr' => 'Mise à disposition de ressources humaines spécifiques ponctuelles',
                'en' => 'Mise à disposition de ressources humaines spécifiques ponctuelles en']
            ],
            'aaaaag' => ['label' => [
                'fr' => 'Partenariat',
                'en' => 'Partenariat en']
            ],
            'aaaaah' => ['label' => [
                'fr' => 'Prototypage',
                'en' => 'Prototypage en']
            ],
            'aaaaai' => ['label' => [
                'fr' => 'R&D contractuelle',
                'en' => 'R&D contractuelle en']
            ],
            'aaaaaj' => ['label' => [
                'fr' => 'Recherche partenariale',
                'en' => 'Recherche partenariale en']
            ],
            'aaaaak' => ['label' => [
                'fr' => 'Simulation, modélisation, calcul',
                'en' => 'Simulation, modélisation, calcul en']
            ],
            'aaaaal' => ['label' => [
                'fr' => 'Transfert de technologies',
                'en' => 'Transfert de technologies en']
            ],
        ]);

        $this->assertEquals($nomenclature, $importer->import('Offres et besoins', __DIR__.'/offres_besoins.csv'));
    }
}
