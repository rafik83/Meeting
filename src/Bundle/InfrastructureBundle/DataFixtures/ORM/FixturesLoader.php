<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\InfrastructureBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\Fixtures;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Fixtures loader.
 */
class FixturesLoader extends AbstractFixture implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $files = [
            __DIR__ . '/Event.yml',
            __DIR__ . '/Nomenclature.yml',
            __DIR__ . '/Template.yml',
            __DIR__ . '/Type.yml',
            __DIR__ . '/Category.yml',
            __DIR__ . '/Sheet.yml',
            __DIR__ . '/Rule.yml',
            __DIR__ . '/User.yml',
            __DIR__ . '/Participant.yml',
            __DIR__ . '/Schedule.yml',
            __DIR__ . '/Meeting/Request.yml',
            __DIR__ . '/Meeting/Proposition.yml',
            __DIR__ . '/CanceledRequestNotification.yml',
        ];

        $options = [
            'locale'    => 'fr_FR',
            'providers' => [
                $this->container->get('vimeet_infrastructure.data_fixtures_orm.provider'),
            ],
        ];

        Fixtures::load($files, $manager, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
