<?php declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add analytics columns
 */
final class Version20200914142539 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sheet ADD analytics_views INT NOT NULL, ADD analytics_unique_views INT NOT NULL, ADD analytics_viewers LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', ADD analytics_clicked_elements LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sheet DROP analytics_views, DROP analytics_unique_views, DROP analytics_viewers, DROP analytics_clicked_elements');
    }
}
