<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Migration create spot_unavailability table
 */
class Version20170127153413 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // Create spot_unavailability table
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE spot_unavailability (id INT AUTO_INCREMENT NOT NULL, spot_id INT DEFAULT NULL, slot_id INT DEFAULT NULL, INDEX IDX_7262A9442DF1D37C (spot_id), INDEX IDX_7262A94459E5119C (slot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE spot_unavailability ADD CONSTRAINT FK_7262A9442DF1D37C FOREIGN KEY (spot_id) REFERENCES spot (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE spot_unavailability ADD CONSTRAINT FK_7262A94459E5119C FOREIGN KEY (slot_id) REFERENCES meeting_slot (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // Drop spot_unavailability table
        $this->abortIf('mysql' != $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE spot_unavailability');
    }
}
