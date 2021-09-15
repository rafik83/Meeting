<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Fix mass_unavailability_types indexes
 */
class Version20181217143631 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mass_unavailability_types DROP FOREIGN KEY FK_108BB78EC54C8C93');
        $this->addSql('ALTER TABLE mass_unavailability_types DROP FOREIGN KEY FK_108BB78EEA5DA7EB');
        $this->addSql('DROP INDEX idx_108bb78eea5da7eb ON mass_unavailability_types');
        $this->addSql('CREATE INDEX IDX_796BCB5BEA5DA7EB ON mass_unavailability_types (mass_id)');
        $this->addSql('DROP INDEX idx_108bb78ec54c8c93 ON mass_unavailability_types');
        $this->addSql('CREATE INDEX IDX_796BCB5BC54C8C93 ON mass_unavailability_types (type_id)');
        $this->addSql('ALTER TABLE mass_unavailability_types ADD CONSTRAINT FK_108BB78EC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mass_unavailability_types ADD CONSTRAINT FK_108BB78EEA5DA7EB FOREIGN KEY (mass_id) REFERENCES mass_unavailability (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE mass_unavailability_types DROP FOREIGN KEY FK_796BCB5BEA5DA7EB');
        $this->addSql('ALTER TABLE mass_unavailability_types DROP FOREIGN KEY FK_796BCB5BC54C8C93');
        $this->addSql('DROP INDEX idx_796bcb5bea5da7eb ON mass_unavailability_types');
        $this->addSql('CREATE INDEX IDX_108BB78EEA5DA7EB ON mass_unavailability_types (mass_id)');
        $this->addSql('DROP INDEX idx_796bcb5bc54c8c93 ON mass_unavailability_types');
        $this->addSql('CREATE INDEX IDX_108BB78EC54C8C93 ON mass_unavailability_types (type_id)');
        $this->addSql('ALTER TABLE mass_unavailability_types ADD CONSTRAINT FK_796BCB5BEA5DA7EB FOREIGN KEY (mass_id) REFERENCES mass_unavailability (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mass_unavailability_types ADD CONSTRAINT FK_796BCB5BC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
    }
}
