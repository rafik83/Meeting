<?php

declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20210428141122 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'add localized urls to custom links';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE custom_link_localized_url (id INT AUTO_INCREMENT NOT NULL, custom_link_id INT DEFAULT NULL, locale VARCHAR(255) NOT NULL, url VARCHAR(5000) NOT NULL, INDEX IDX_41B27FA5AAFD6BBC (custom_link_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE custom_link_localized_url ADD CONSTRAINT FK_41B27FA5AAFD6BBC FOREIGN KEY (custom_link_id) REFERENCES custom_link (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE custom_link DROP url');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE custom_link_localized_url');
        $this->addSql('ALTER TABLE custom_link ADD url VARCHAR(255) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
    }
}
