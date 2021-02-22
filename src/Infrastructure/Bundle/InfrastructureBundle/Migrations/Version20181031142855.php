<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add Type Content and Type Content Translation tables
 */
class Version20181031142855 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE type_content (id INT AUTO_INCREMENT NOT NULL, associated_participation_type_id INT NOT NULL, type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_D6E45F5CCBD407F0 (associated_participation_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_content_translation (id INT AUTO_INCREMENT NOT NULL, content_id INT DEFAULT NULL, locale VARCHAR(255) NOT NULL, value LONGTEXT NOT NULL, INDEX IDX_61F75C4684A0A3ED (content_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE type_content ADD CONSTRAINT FK_D6E45F5CCBD407F0 FOREIGN KEY (associated_participation_type_id) REFERENCES type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE type_content_translation ADD CONSTRAINT FK_61F75C4684A0A3ED FOREIGN KEY (content_id) REFERENCES type_content (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE type_content_translation DROP FOREIGN KEY FK_61F75C4684A0A3ED');
        $this->addSql('DROP TABLE type_content');
        $this->addSql('DROP TABLE type_content_translation');
    }
}
