<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create table `sheet_viewed` to flag sheet viewed by user
 */
class Version20170504114616 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sheet_viewed (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, sheet_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_58E1575AA76ED395 (user_id), INDEX IDX_58E1575A8B1206A5 (sheet_id), UNIQUE INDEX unique_user_sheet (user_id, sheet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sheet_viewed ADD CONSTRAINT FK_58E1575AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sheet_viewed ADD CONSTRAINT FK_58E1575A8B1206A5 FOREIGN KEY (sheet_id) REFERENCES sheet (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sheet_viewed');
    }
}
