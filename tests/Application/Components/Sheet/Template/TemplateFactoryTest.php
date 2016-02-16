<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Components\Sheet\Template;

use Proximum\Vimeet\Application\Components\Sheet\Template\Group;
use Proximum\Vimeet\Application\Components\Sheet\Template\Template;
use Proximum\Vimeet\Application\Components\Sheet\Template\TemplateFactory;
use Proximum\Vimeet\Application\Components\Sheet\Template\Type\LibTextType;

class TemplateFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateTemplateFromArray()
    {
        $factory = new TemplateFactory();

        $actual = $factory->createTemplateFromArray([
            '563caf1d9babc' => [
                'label'    => [],
                'position' => 1,
                'template' => [
                    '563caf1d9b1cb' => [
                        'type'                => 'lib_text',
                        'required'            => true,
                        'private'             => false,
                        'label'               => ['fr' => 'Nom', 'en' => 'Lastname'],
                        'tags'                => ['participant_lastname', 'participant'],
                        'updatableUntil'      => null,
                        'translationRequired' => false,
                        'translatable'        => false,
                        'position'            => 1,
                    ],
                    '563caf2746398' => [
                        'type'                => 'lib_text',
                        'required'            => true,
                        'private'             => false,
                        'label'               => ['fr' => 'Prénom', 'en' => 'Firstname'],
                        'tags'                => ['participant_firstname', 'participant'],
                        'updatableUntil'      => null,
                        'translationRequired' => false,
                        'translatable'        => false,
                        'position'            => 2,
                    ],
                ],
            ],
        ]);

        $type_563caf1d9b1cb = new LibTextType('563caf1d9b1cb');
        $type_563caf1d9b1cb->setOptions([
            'type'                => 'lib_text',
            'required'            => true,
            'private'             => false,
            'label'               => ['fr' => 'Nom', 'en' => 'Lastname'],
            'tags'                => ['participant_lastname', 'participant'],
            'updatableUntil'      => null,
            'translationRequired' => false,
            'translatable'        => false,
            'position'            => 1,
        ]);

        $type_563caf2746398 = new LibTextType('563caf2746398');
        $type_563caf2746398->setOptions([
            'type'                => 'lib_text',
            'required'            => true,
            'private'             => false,
            'label'               => ['fr' => 'Prénom', 'en' => 'Firstname'],
            'tags'                => ['participant_firstname', 'participant'],
            'updatableUntil'      => null,
            'translationRequired' => false,
            'translatable'        => false,
            'position'            => 2,
        ]);

        $group_563caf1d9babc = new Group('563caf1d9babc');
        $group_563caf1d9babc->setOptions([
            'label'    => [],
            'position' => 1,
            'template' => [
                '563caf1d9b1cb' => [
                    'type'                => 'lib_text',
                    'required'            => true,
                    'private'             => false,
                    'label'               => ['fr' => 'Nom', 'en' => 'Lastname'],
                    'tags'                => ['participant_lastname', 'participant'],
                    'updatableUntil'      => null,
                    'translationRequired' => false,
                    'translatable'        => false,
                    'position'            => 1,
                ],
                '563caf2746398' => [
                    'type'                => 'lib_text',
                    'required'            => true,
                    'private'             => false,
                    'label'               => ['fr' => 'Prénom', 'en' => 'Firstname'],
                    'tags'                => ['participant_firstname', 'participant'],
                    'updatableUntil'      => null,
                    'translationRequired' => false,
                    'translatable'        => false,
                    'position'            => 2,
                ],
            ],
        ]);

        $expected = (new Template())
            ->addGroup($group_563caf1d9babc
                ->addType($type_563caf1d9b1cb)
                ->addType($type_563caf2746398)
            );

        $this->assertEquals($expected, $actual);
        $this->assertEquals($group_563caf1d9babc, $actual->getGroup('563caf1d9babc'));
        $this->assertEquals($type_563caf1d9b1cb, $actual->getGroup('563caf1d9babc')->getType('563caf1d9b1cb'));
        $this->assertEquals($type_563caf2746398, $actual->getGroup('563caf1d9babc')->getType('563caf2746398'));
        $this->assertEquals([$type_563caf2746398], $actual->getTypesByTag('participant_firstname'));
        $this->assertEquals([$type_563caf1d9b1cb, $type_563caf2746398], $actual->getTypesByTag('participant'));
    }

    public function testCreateTemplateFromArrayWithoutGroup()
    {
        $factory = new TemplateFactory();

        $actual = $factory->createTemplateFromArray([
            '563caf1d9b1cb' => [
                'type'                => 'lib_text',
                'required'            => true,
                'private'             => false,
                'label'               => ['fr' => 'Nom', 'en' => 'Lastname'],
                'tags'                => ['participant_lastname'],
                'updatableUntil'      => null,
                'translationRequired' => false,
                'translatable'        => false,
                'position'            => 1,
            ],
            '563caf2746398' => [
                'type'                => 'lib_text',
                'required'            => true,
                'private'             => false,
                'label'               => ['fr' => 'Prénom', 'en' => 'Firstname'],
                'tags'                => ['participant_firstname'],
                'updatableUntil'      => null,
                'translationRequired' => false,
                'translatable'        => false,
                'position'            => 2,
            ],

        ]);

        $type_563caf1d9b1cb = new LibTextType('563caf1d9b1cb');
        $type_563caf1d9b1cb->setOptions([
            'type'                => 'lib_text',
            'required'            => true,
            'private'             => false,
            'label'               => ['fr' => 'Nom', 'en' => 'Lastname'],
            'tags'                => ['participant_lastname'],
            'updatableUntil'      => null,
            'translationRequired' => false,
            'translatable'        => false,
            'position'            => 1,
        ]);

        $type_563caf2746398 = new LibTextType('563caf2746398');
        $type_563caf2746398->setOptions([
            'type'                => 'lib_text',
            'required'            => true,
            'private'             => false,
            'label'               => ['fr' => 'Prénom', 'en' => 'Firstname'],
            'tags'                => ['participant_firstname'],
            'updatableUntil'      => null,
            'translationRequired' => false,
            'translatable'        => false,
            'position'            => 2,
        ]);

        $group_563caf1d9babc = new Group('default');
        $group_563caf1d9babc->setOptions([
            'label'    => 'Default',
            'position' => 1,
            'template' => [
                '563caf1d9b1cb' => [
                    'type'                => 'lib_text',
                    'required'            => true,
                    'private'             => false,
                    'label'               => ['fr' => 'Nom', 'en' => 'Lastname'],
                    'tags'                => ['participant_lastname'],
                    'updatableUntil'      => null,
                    'translationRequired' => false,
                    'translatable'        => false,
                    'position'            => 1,
                ],
                '563caf2746398' => [
                    'type'                => 'lib_text',
                    'required'            => true,
                    'private'             => false,
                    'label'               => ['fr' => 'Prénom', 'en' => 'Firstname'],
                    'tags'                => ['participant_firstname'],
                    'updatableUntil'      => null,
                    'translationRequired' => false,
                    'translatable'        => false,
                    'position'            => 2,
                ],
            ],
        ]);

        $expected = (new Template())
            ->addGroup($group_563caf1d9babc
                ->addType($type_563caf1d9b1cb)
                ->addType($type_563caf2746398)
            );

        $this->assertEquals($expected, $actual);
        $this->assertEquals($group_563caf1d9babc, $actual->getGroup('default'));
        $this->assertEquals($type_563caf1d9b1cb, $actual->getGroup('default')->getType('563caf1d9b1cb'));
        $this->assertEquals($type_563caf2746398, $actual->getGroup('default')->getType('563caf2746398'));
    }
}
