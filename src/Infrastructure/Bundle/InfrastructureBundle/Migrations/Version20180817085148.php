<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add timezone on Participant
 */
class Version20180817085148 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE participant ADD timezone VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE participant DROP timezone');
    }
}
