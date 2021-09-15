<?php

declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210506141731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add meeting request inactivation by "who see what"';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rule ADD disable_meeting_request TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rule DROP disable_meeting_request');
    }
}
