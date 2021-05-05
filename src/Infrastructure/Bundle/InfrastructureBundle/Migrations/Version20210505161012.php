<?php

declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210505161012 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add field to store the list of sheets that received a followup mail after a meeting';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE meeting ADD followup_sent_sheet_ids LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE meeting DROP followup_sent_sheet_ids');
    }
}
