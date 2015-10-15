<?php

namespace Proximum\Vimeet\Domain\Model;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\Fixtures;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Fixtures loader
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
        ];

        $options = [
            'locale'    => 'fr_FR',
            'providers' => [$this],
        ];

        Fixtures::load($files, $manager, $options);
    }

    public function domain()
    {
        return $this->container->getParameter('domain');
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
