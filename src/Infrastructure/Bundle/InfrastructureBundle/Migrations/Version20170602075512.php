<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Change sheet title to nullable
 */
class Version20170602075512 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE sheet CHANGE title title VARCHAR(255) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE sheet CHANGE title title VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
    }
}
