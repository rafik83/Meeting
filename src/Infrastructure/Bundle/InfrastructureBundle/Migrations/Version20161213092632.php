<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * This migration create the happening_question table
 * with foreign keys on a user, a sheet and an happening
 */
class Version20161213092632 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE happening_question ADD sheet_id INT DEFAULT NULL, ADD created_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE happening_question ADD CONSTRAINT FK_BCCF876D8B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE happening_question ADD CONSTRAINT FK_BCCF876DB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_BCCF876D8B1206A5 ON happening_question (sheet_id)');
        $this->addSql('CREATE INDEX IDX_BCCF876DB03A8386 ON happening_question (created_by_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE happening_question DROP FOREIGN KEY FK_BCCF876D8B1206A5');
        $this->addSql('ALTER TABLE happening_question DROP FOREIGN KEY FK_BCCF876DB03A8386');
        $this->addSql('DROP INDEX IDX_BCCF876D8B1206A5 ON happening_question');
        $this->addSql('DROP INDEX IDX_BCCF876DB03A8386 ON happening_question');
        $this->addSql('ALTER TABLE happening_question DROP sheet_id, DROP created_by_id');
    }
}
