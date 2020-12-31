<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Add Event on Tip
 */
class Version20171130095742 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tip ADD event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tip ADD CONSTRAINT FK_4883B84C71F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4883B84C71F7E88B ON tip (event_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE tip DROP FOREIGN KEY FK_4883B84C71F7E88B');
        $this->addSql('DROP INDEX IDX_4883B84C71F7E88B ON tip');
        $this->addSql('ALTER TABLE tip DROP event_id');
    }

    /**
     * Remove old tips with types assigned, to attached them the event and keep global tip without types
     *
     * @param Schema $schema
     */
    public function postUp(Schema $schema)
    {
        /** @var Tip[] $tipsWithoutEventWithType */
        $tipsWithoutEventWithType = $this->container->get('repository.tip.repository')->getTipWithoutEventWithType();

        $newTips = [];

        foreach ($tipsWithoutEventWithType as $tip) {
            foreach ($tip->getTypes() as $type) {
                if (!isset($newTips[$type->getEvent()->getId()][$tip->getId()])) {
                    $newTip = new Tip(
                        $tip->getTitle(),
                        $type->getEvent(),
                        $tip->isOnMeetingManagement(),
                        $tip->isOnCatalog(),
                        $tip->isOnPrintPlanning(),
                        $tip->isOnSheet(),
                        $tip->isOnAgenda(),
                        $tip->isOnProgram(),
                        $tip->isOnConfirmationPhone(),
                        $tip->getCreatedAt()
                    );

                    $newTip->setType($type);
                    foreach ($tip->getTranslations() as $translation) {
                        $newTip->translate(
                            $translation->getLocale(),
                            $translation->getTitle(),
                            $translation->getContent(),
                            $translation->getCreatedAt()
                        );
                    }

                    $newTips[$type->getEvent()->getId()][$tip->getId()] = $newTip;
                } else {
                    $newTips[$type->getEvent()->getId()][$tip->getId()]->setType($type);
                }
            }

            $this->container->get('repository.tip.repository')->removeTip($tip);
        }

        foreach ($newTips as $eventTips) {
            foreach ($eventTips as $tip) {
                $this->container->get('repository.tip.repository')->add($tip);
            }
        }
    }
}
