<?php declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Remove path on happening_webinar_record_archive and add webinar_record_zip_file_url on happening
 */
final class Version20200818104413 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE happening_webinar_record_archive DROP path');
        $this->addSql('ALTER TABLE happening ADD webinar_record_zip_file_url LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE happening DROP webinar_record_zip_file_url');
        $this->addSql('ALTER TABLE happening_webinar_record_archive ADD path LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
    }
}
