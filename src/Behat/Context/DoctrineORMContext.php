<?php

namespace Proximum\Vimeet\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class DoctrineORMContext implements Context
{
    const NotMappedTables = [
        'jms_job_related_entities',
    ];

    private KernelInterface $kernel;
    private EntityManagerInterface $entityManager;

    public function __construct(KernelInterface $kernel, EntityManagerInterface $entityManager)
    {
        $this->kernel = $kernel;
        $this->entityManager = $entityManager;
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
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        $purger = new ORMPurger($this->entityManager);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $purger->purge();
        $this->purgeNotMappedTables();
        $this->entityManager->clear();
    }

    private function purgeNotMappedTables()
    {
        $platform = $this->entityManager->getConnection()->getDatabasePlatform();
        $connection = $this->entityManager->getConnection();

        foreach (self::NotMappedTables as $tableName) {
            $connection->executeQuery($platform->getTruncateTableSQL($tableName, true));
        }
    }
}
