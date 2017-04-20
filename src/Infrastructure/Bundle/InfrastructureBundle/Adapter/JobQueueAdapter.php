<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter;

use JMS\JobQueueBundle\Entity\Job;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\GenerateInvoiceCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\IndexSheetsCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Order\ExportOrderCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Planner\ExportPlannerCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Planner\ImportPlannerCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Sheet\Index\IndexInCatalogSheetsByEventCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Sheet\Index\IndexSheetsByRegistrationTemplateCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Sheet\Index\IndexSheetsBySheetTemplateCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Sheet\Index\IndexSheetsByTypesCommand;

class JobQueueAdapter extends AbstractJobQueueAdapter implements JobQueueInterface
{
    /**
     * {@inheritdoc}
     */
    public function sendCampaign(Campaign $campaign)
    {
        $job = new Job('vimeet:campaign:send', [$campaign->getId()]);
        $job->addRelatedEntity($campaign);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function printPlanning(array $types, $orderBy, $emailToNotify, $locale)
    {
        $typeOptions = array_map(function (Type $type) {
            return sprintf('--types=%s', $type->getId());
        }, $types);

        $job = new Job(
            'vimeet:planning:generate',
            array_merge(
                $typeOptions,
                [
                    sprintf('--orderBy=%s', $orderBy),
                    sprintf('--emailToNotify=%s', $emailToNotify),
                    sprintf('--locale=%s', $locale),
                ]
            )
        );

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function generateInvoice(array $sheetIds, Admin $admin)
    {
        $job = new Job(GenerateInvoiceCommand::NAME, [
            'adminId'  => $admin->getId(),
            'sheetIds' => implode(',', $sheetIds),
        ]);

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function exportOrdersForEvent(Event $event, Admin $admin, $locale)
    {
        $job = new Job(ExportOrderCommand::NAME, [$event->getId(), $admin->getEmail(), $locale]);

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function exportPlannerForEvent(Event $event, Admin $admin, $locale, $lockMeetingRequest, $solutionType)
    {
        $job = new Job(ExportPlannerCommand::NAME, [
            $event->getId(),
            $admin->getEmail(),
            $locale,
            $lockMeetingRequest,
            $solutionType
        ]);

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function importPlannerForEvent(File $file, Event $event, Admin $admin, $locale)
    {
        $job = new Job(ImportPlannerCommand::NAME, [
            $file->getId(),
            $event->getId(),
            $admin->getEmail(),
            $locale
        ]);

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function indexSheetsBySheetTemplate(SheetTemplate $sheetTemplate)
    {
        $job = new Job(IndexSheetsBySheetTemplateCommand::NAME, [$sheetTemplate->getId()]);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function indexSheetsByRegistrationTemplate(RegistrationTemplate $registrationTemplate)
    {
        $job = new Job(IndexSheetsByRegistrationTemplateCommand::NAME, [$registrationTemplate->getId()]);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function indexSheetsByTypes(array $typeIds)
    {
        $job = new Job(IndexSheetsByTypesCommand::NAME, [implode(',', $typeIds)]);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function indexInCatalogSheetsByEvent(Event $event)
    {
        $job = new Job(IndexInCatalogSheetsByEventCommand::NAME, [$event->getId()]);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function indexSheets(array $sheetIds)
    {
        $job = new Job(
            IndexSheetsCommand::NAME,
            [implode(',', $sheetIds)],
            true,
            Job::DEFAULT_QUEUE,
            Job::PRIORITY_HIGH
        );
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function sendEmailing(Event $event, array $sheetIds)
    {
        $job = new Job(
            SendEmailingCommand::NAME,
            [$event->getId(), implode(',', $sheetIds)]
        );
        $this->setJob($job);
    }
}
