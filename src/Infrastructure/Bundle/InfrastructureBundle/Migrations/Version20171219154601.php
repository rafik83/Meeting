<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add package_participant ; Remove participant from Package
 */
class Version20171219154601 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE package_participant (id INT AUTO_INCREMENT NOT NULL, package_id INT NOT NULL, product_participant_id INT NOT NULL, rank INT NOT NULL, INDEX IDX_1A447177F44CABFF (package_id), INDEX IDX_1A447177CE754E35 (product_participant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE package_participant ADD CONSTRAINT FK_1A447177F44CABFF FOREIGN KEY (package_id) REFERENCES package (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE package_participant ADD CONSTRAINT FK_1A447177CE754E35 FOREIGN KEY (product_participant_id) REFERENCES product (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function postUp(Schema $schema)
    {
        $this->connection->executeQuery(
            'INSERT INTO package_participant (package_id, product_participant_id, rank)
                SELECT package.id, package.participant_id, 0
                FROM package
                WHERE package.participant_id IS NOT NULL'
        );

        $this->connection->executeQuery('ALTER TABLE package DROP FOREIGN KEY FK_DE6867959D1C3019');
        $this->connection->executeQuery('DROP INDEX IDX_DE6867959D1C3019 ON package');
        $this->connection->executeQuery('ALTER TABLE package DROP participant_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE package ADD participant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE package ADD CONSTRAINT FK_DE6867959D1C3019 FOREIGN KEY (participant_id) REFERENCES product (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_DE6867959D1C3019 ON package (participant_id)');
    }

    /**
     * @param Schema $schema
     */
    public function postDown(Schema $schema)
    {
        $this->connection->executeQuery(
            'UPDATE package set package.participant_id = (
                SELECT package_participant.product_participant_id from package_participant
                WHERE package_participant.package_id = package.id)'
        );

        $this->connection->executeQuery('DROP TABLE package_participant');
    }
}
