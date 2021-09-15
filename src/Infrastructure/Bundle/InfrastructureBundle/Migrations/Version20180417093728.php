<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add ProductAttributedToParticipant table
 */
class Version20180417093728 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE product_attributed_to_participant (product_id INT NOT NULL, participant_id INT NOT NULL, order_row_id INT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_25EABF504584665A (product_id), INDEX IDX_25EABF509D1C3019 (participant_id), INDEX IDX_25EABF50402D7927 (order_row_id), PRIMARY KEY(product_id, participant_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_attributed_to_participant ADD CONSTRAINT FK_25EABF504584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_attributed_to_participant ADD CONSTRAINT FK_25EABF509D1C3019 FOREIGN KEY (participant_id) REFERENCES participant (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE product_attributed_to_participant ADD CONSTRAINT FK_25EABF50402D7927 FOREIGN KEY (order_row_id) REFERENCES order_row (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE product_attributed_to_participant');
    }
}
