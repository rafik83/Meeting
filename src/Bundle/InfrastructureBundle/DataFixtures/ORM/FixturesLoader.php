<?php

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\Fixtures;

/**
 * Fixtures loader
 */
class FixturesLoader extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $files = [
            __DIR__ . '/Event.yml',
        ];

        $options = [
            'locale'    => 'fr_FR',
            'providers' => [$this],
        ];

        Fixtures::load($files, $manager, $options);
    }
}
