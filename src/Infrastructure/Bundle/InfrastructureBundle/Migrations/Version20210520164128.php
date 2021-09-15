<?php

declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210520164128 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Add internal ref for ccip payment';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE transaction ADD internal_reference VARCHAR(20000) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE transaction DROP internal_reference');
    }
}
