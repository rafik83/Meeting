<?php

declare(strict_types=1);

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210526080004 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Entities for webinar polls';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE happening_poll (id INT AUTO_INCREMENT NOT NULL, happening_id INT DEFAULT NULL, created_by_id INT DEFAULT NULL, title VARCHAR(1000) NOT NULL, created_at DATETIME NOT NULL, status VARCHAR(255) NOT NULL, multiple_choice TINYINT(1) NOT NULL, INDEX IDX_DA3A638FB7B10E6D (happening_id), INDEX IDX_DA3A638FB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE happening_poll_choice (id INT AUTO_INCREMENT NOT NULL, poll_id INT DEFAULT NULL, content VARCHAR(1000) NOT NULL, INDEX IDX_6ADEA3243C947C0F (poll_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE happening_poll_vote (poll_choice_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_4F3BC70252514F25 (poll_choice_id), INDEX IDX_4F3BC702A76ED395 (user_id), PRIMARY KEY(poll_choice_id, user_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE happening_poll ADD CONSTRAINT FK_DA3A638FB7B10E6D FOREIGN KEY (happening_id) REFERENCES happening (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE happening_poll ADD CONSTRAINT FK_DA3A638FB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE happening_poll_choice ADD CONSTRAINT FK_6ADEA3243C947C0F FOREIGN KEY (poll_id) REFERENCES happening_poll (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE happening_poll_vote ADD CONSTRAINT FK_4F3BC70252514F25 FOREIGN KEY (poll_choice_id) REFERENCES happening_poll_choice (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE happening_poll_vote ADD CONSTRAINT FK_4F3BC702A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE happening_poll_choice DROP FOREIGN KEY FK_6ADEA3243C947C0F');
        $this->addSql('ALTER TABLE happening_poll_vote DROP FOREIGN KEY FK_4F3BC70252514F25');
        $this->addSql('DROP TABLE happening_poll');
        $this->addSql('DROP TABLE happening_poll_choice');
        $this->addSql('DROP TABLE happening_poll_vote');
    }
}
