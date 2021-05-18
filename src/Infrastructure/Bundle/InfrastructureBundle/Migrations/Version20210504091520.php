<?php

declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210504091520 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add date column to store latest update of evaluation';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE contact ADD evaluated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE contact DROP evaluated_at');
    }
}
