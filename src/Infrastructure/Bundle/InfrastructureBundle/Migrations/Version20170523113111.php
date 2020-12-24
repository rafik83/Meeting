<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create table `tip_type` to manage tip by event and type
 */
class Version20170523113111 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE tip_type (tip_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_4FED94DB476C47F6 (tip_id), INDEX IDX_4FED94DBC54C8C93 (type_id), PRIMARY KEY(tip_id, type_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tip_type ADD CONSTRAINT FK_4FED94DB476C47F6 FOREIGN KEY (tip_id) REFERENCES tip (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tip_type ADD CONSTRAINT FK_4FED94DBC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE tip_type');
    }
}
