<?php

declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210422142200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'add priority to custom links';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE custom_link ADD `priority` INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE custom_link DROP `priority`');
    }
}
