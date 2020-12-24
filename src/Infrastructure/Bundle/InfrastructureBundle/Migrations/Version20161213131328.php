<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * This migration create the happening_question table
 * with foreign keys on a user, a sheet and an happening
 */
class Version20161213131328 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE happening_question (id INT AUTO_INCREMENT NOT NULL, happening_id INT DEFAULT NULL, sheet_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, content VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_BCCF876DB7B10E6D (happening_id), INDEX IDX_BCCF876D8B1206A5 (sheet_id), INDEX IDX_BCCF876DB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE happening_question ADD CONSTRAINT FK_BCCF876DB7B10E6D FOREIGN KEY (happening_id) REFERENCES happening (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE happening_question ADD CONSTRAINT FK_BCCF876D8B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE happening_question ADD CONSTRAINT FK_BCCF876DB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE happening_question');
    }
}
