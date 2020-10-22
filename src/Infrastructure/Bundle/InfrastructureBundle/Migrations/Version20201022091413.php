<?php declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migrate scanned contact's property to origin property
 */
final class Version20201022091413 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE contact ADD origin VARCHAR(20) NOT NULL');
        $this->addSql('UPDATE contact SET contact.origin = IF(contact.scanned = 0, \'meeting\', \'scan\')');
        $this->addSql('ALTER TABLE contact DROP scanned');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql('ALTER TABLE contact ADD ALTER TABLE contact ADD scanned TINYINT(1) NOT NULL');
        $this->addSql('UPDATE contact SET contact.scanned = IF(contact.origin = \'scan\', 1, 0)');
        $this->addSql('ALTER TABLE contact DROP origin');
    }
}
