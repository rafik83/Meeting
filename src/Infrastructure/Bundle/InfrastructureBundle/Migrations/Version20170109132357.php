<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Change meeting / request mapping ; Add spot to Meeting
 */
class Version20170109132357 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE meeting_request DROP INDEX IDX_A345C71267433D9C, ADD UNIQUE INDEX UNIQ_A345C71267433D9C (meeting_id)');
        $this->addSql('ALTER TABLE meeting ADD spot_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE meeting ADD CONSTRAINT FK_F515E1392DF1D37C FOREIGN KEY (spot_id) REFERENCES spot (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_F515E1392DF1D37C ON meeting (spot_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE meeting DROP FOREIGN KEY FK_F515E1392DF1D37C');
        $this->addSql('DROP INDEX IDX_F515E1392DF1D37C ON meeting');
        $this->addSql('ALTER TABLE meeting DROP spot_id');
        $this->addSql('ALTER TABLE meeting_request DROP INDEX UNIQ_A345C71267433D9C, ADD INDEX IDX_A345C71267433D9C (meeting_id)');
    }
}
