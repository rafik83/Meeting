<?php

declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210415073213 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create table for custom links';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE custom_link (id INT AUTO_INCREMENT NOT NULL, static_formulation_id INT DEFAULT NULL, event_id INT DEFAULT NULL, url VARCHAR(255) NOT NULL, icon_name VARCHAR(255) NOT NULL, icon_color VARCHAR(255) NOT NULL, label_color VARCHAR(255) NOT NULL, button_color VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_37DA510CE3015C22 (static_formulation_id), INDEX IDX_37DA510C71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE custom_link ADD CONSTRAINT FK_37DA510CE3015C22 FOREIGN KEY (static_formulation_id) REFERENCES static_formulation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE custom_link ADD CONSTRAINT FK_37DA510C71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE custom_link');
    }
}
