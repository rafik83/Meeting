<?php declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add visible option for HappeningParticipation
 */
final class Version20201216171737 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE happening_participation ADD visible TINYINT(1) DEFAULT \'1\' NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE happening_participation DROP visible');
    }
}
