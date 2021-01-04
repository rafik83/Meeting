<?php

namespace Proximum\Vimeet\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class DoctrineORMContext implements Context, KernelAwareContext
{
    const NotMappedTables = [
        'jms_job_related_entities',
    ];

    /** @var KernelInterface */
    private $kernel;

    /**
     * {@inheritdoc}
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @BeforeSuite
     *
     * @param BeforeSuiteScope $scope
     */
    public static function prepare(BeforeSuiteScope $scope)
    {
        exec('bin/console doctrine:schema:update --force --env=test');
    }

    /**
     * @Given the database is purged
     */
    public function purgeDatabase()
    {
        $entityManager = $this->getEntityManager();
        $entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        $purger = new ORMPurger($entityManager);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $purger->purge();
        $this->purgeNotMappedTables();
        $entityManager->clear();
    }

    private function purgeNotMappedTables()
    {
        $entityManager = $this->getEntityManager();
        $platform = $entityManager->getConnection()->getDatabasePlatform();
        $connection = $entityManager->getConnection();

        foreach (self::NotMappedTables as $tableName) {
            $connection->executeUpdate($platform->getTruncateTableSQL($tableName, true));
        }
    }

    /**
     * @return EntityManagerInterface
     */
    private function getEntityManager()
    {
        return $this->kernel->getContainer()->get('doctrine.orm.entity_manager');
    }
}
