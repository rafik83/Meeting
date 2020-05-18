<?php declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add VisioSettings and VisioSettingsTranslation table.
 */
final class Version20200514084334 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE visio_settings (id INT AUTO_INCREMENT NOT NULL, event_id INT DEFAULT NULL, UNIQUE INDEX UNIQ_3F5D04CD71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE visio_settings_translation (id INT AUTO_INCREMENT NOT NULL, visio_settings_id INT DEFAULT NULL, locale VARCHAR(255) NOT NULL, header VARCHAR(255) DEFAULT NULL, INDEX IDX_29645F168E802851 (visio_settings_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE visio_settings ADD CONSTRAINT FK_3F5D04CD71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE visio_settings_translation ADD CONSTRAINT FK_29645F168E802851 FOREIGN KEY (visio_settings_id) REFERENCES visio_settings (id)');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE visio_settings_translation DROP FOREIGN KEY FK_29645F168E802851');
        $this->addSql('DROP TABLE visio_settings');
        $this->addSql('DROP TABLE visio_settings_translation');
    }
}
