<?php

namespace Proximum\Vimeet\Behat\Context;

use Behat\Behat\Context\Context;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineORMContext implements Context
{
    const NotMappedTables = [
        'messenger_jobs',
    ];

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
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
        $this->entityManager->getConnection()->executeQuery('SET foreign_key_checks = 0;');
        $purger->purge();
        $this->purgeNotMappedTables();
        $this->entityManager->getConnection()->executeQuery('SET foreign_key_checks = 1;');
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
