<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add PaymentConditionsTranslation table
 */
class Version20181105160605 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE type_payment_conditions_translation (id INT AUTO_INCREMENT NOT NULL, payment_conditions_id INT NOT NULL, locale VARCHAR(255) NOT NULL, bank_info LONGTEXT NOT NULL, billing_address LONGTEXT NOT NULL, payment_condition LONGTEXT NOT NULL, payment_footer LONGTEXT NOT NULL, INDEX IDX_EF3913A438FFD5C2 (payment_conditions_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE type_payment_conditions_translation ADD CONSTRAINT FK_EF3913A438FFD5C2 FOREIGN KEY (payment_conditions_id) REFERENCES type_payment_conditions (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE type_payment_conditions_translation');
    }
}
