<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add sessions table
 */
class Version20160829173500 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE IF NOT EXISTS `sessions` ( `sess_id` VARBINARY(128) NOT NULL PRIMARY KEY, `sess_data` BLOB NOT NULL, `sess_time` INTEGER UNSIGNED NOT NULL, `sess_lifetime` MEDIUMINT NOT NULL) COLLATE utf8_bin, ENGINE = InnoDB;');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE `sessions`');
    }
}
