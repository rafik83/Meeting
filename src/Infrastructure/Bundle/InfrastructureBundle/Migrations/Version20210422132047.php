<?php

declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add sendEmailMinEvaluation to Rule entity
 */
final class Version20210422132047 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE rule ADD send_email_min_evaluation SMALLINT DEFAULT 5');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE rule DROP send_email_min_evaluation');
    }
}
