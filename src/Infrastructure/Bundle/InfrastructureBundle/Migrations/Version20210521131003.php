<?php

declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20210521131003 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add boolean flag to allow poll in webinar';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE happening ADD poll_allowed TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE happening DROP poll_allowed TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
