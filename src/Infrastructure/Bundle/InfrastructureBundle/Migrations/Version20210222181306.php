<?php

declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create tables for messenger jobs
 */
final class Version20210222181306 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE messenger_jobs (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, headers LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, queue_name VARCHAR(190) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), INDEX IDX_75EA56E0FB7336F0 (queue_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE messenger_jobs');
    }
}
