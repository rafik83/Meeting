<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20171204112100 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // Nothing to up
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // Nothing to down
    }

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
