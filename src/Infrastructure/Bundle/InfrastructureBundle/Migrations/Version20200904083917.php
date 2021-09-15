<?php declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Happening: add hls option and introduce HappeningBroadcast model.
 */
final class Version20200904083917 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE happening_broadcast (id INT AUTO_INCREMENT NOT NULL, happening_id INT DEFAULT NULL, is_stopped TINYINT(1) NOT NULL, broadcast_id VARCHAR(255) NOT NULL, hls_url VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, end_at DATETIME NOT NULL, INDEX IDX_399C3549B7B10E6D (happening_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE happening_broadcast ADD CONSTRAINT FK_399C3549B7B10E6D FOREIGN KEY (happening_id) REFERENCES happening (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE happening ADD allow_hls TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE happening_broadcast');
        $this->addSql('ALTER TABLE happening DROP allow_hls');
    }
}
