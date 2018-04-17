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
use Proximum\Vimeet\Domain\Model\PlannerJob;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Aggregate\FullUnavailability\UsersFullUnavailabilityAggregateCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Aggregate\FullUnavailability\UsersFullUnavailabilityByEventAggregateCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Aggregate\Participant\ParticipantAssignedToRequestAggregateCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Aggregate\Sheet\AvailableSlotCalculatorCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Aggregate\Sheet\Phone\PhoneValidationStatusCalculatorCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Analytic\MeetingSolution\GenerateMeetingSolutionCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Event\IndexFromScratchCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Event\Sheet\IndexSheetsByEventCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Event\User\Agenda\Version\GenerateVersionsCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\GenerateInvoiceCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\IndexSheetsCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\OMZ\ExportUserCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Order\ExportOrderCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Planner\ExportPlannerCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Planner\ImportPlannerCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Planning\GeneratePlanningCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\SendEmailingCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Sheet\PrintPdfCommand;
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
    public function printPlanning(Event\ExtraData $extraData, string $orderBy, $emailToNotify, $locale): void
    {
        $job = new Job(
            GeneratePlanningCommand::NAME,
            [
                sprintf('--sheetIdsExtraData=%s', $extraData->getId()),
                sprintf('--orderBy=%s', $orderBy),
                sprintf('--emailToNotify=%s', $emailToNotify),
                sprintf('--locale=%s', $locale),
            ]
        );

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function printSheetsPdf(
        Event $event,
        array $sheetIds,
        string $emailToNotify,
        string $locale,
        string $orderBy
    ) {
        $job = new Job(PrintPdfCommand::NAME, [
            sprintf('--sheetIds=%s', implode(',', $sheetIds)),
            sprintf('--eventId=%s', $event->getId()),
            sprintf('--emailToNotify=%s', $emailToNotify),
            sprintf('--locale=%s', $locale),
            sprintf('--orderBy=%s', $orderBy)
        ]);

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function generateInvoice(Event $event, array $sheetIds, Admin $admin)
    {
        $job = new Job(GenerateInvoiceCommand::NAME, [
            'eventId'  => $event->getId(),
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
    public function exportPlannerForEvent(
        Event $event,
        Admin $admin,
        string $locale,
        bool $lockMeetingRequest,
        string $solutionType,
        bool $isModeAuto,
        ?PlannerJob $plannerJob
    ) {
        $job = new Job(
            ExportPlannerCommand::NAME,
            [
                $event->getId(),
                $admin->getEmail(),
                $locale,
                $solutionType,
                true === $lockMeetingRequest
                    ? ExportPlannerCommand::LOCK_MEETING_REQUEST
                    : ExportPlannerCommand::DONT_LOCK_MEETING_REQUEST,
                true === $isModeAuto
                    ? ExportPlannerCommand::MODE_AUTO
                    : ExportPlannerCommand::MODE_MANUAL,
                null !== $plannerJob ? $plannerJob->getId() : null
            ]
        );

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function importPlannerForEvent(
        File $file,
        Event $event,
        Admin $admin,
        $locale,
        ?PlannerJob $plannerJob = null
    ) {
        $job = new Job(ImportPlannerCommand::NAME, [
            $file->getId(),
            $event->getId(),
            $admin->getEmail(),
            $locale,
            $plannerJob instanceof PlannerJob ? $plannerJob->getId() : null
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
    public function aggregateEventUsersFullUnavailability(Event $event, $onlyInCatalog = false)
    {
        $job = new Job(UsersFullUnavailabilityByEventAggregateCommand::NAME, [
            $event->getId(),
            $onlyInCatalog
        ]);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function aggregateUsersFullUnavailability(Event $event, array $users)
    {
        if (!empty($users)) {
            $job = new Job(UsersFullUnavailabilityAggregateCommand::NAME, [
                $event->getId(),
                implode(',', array_map(function (User $user) {
                    return $user->getId();
                }, $users))
            ]);
            $this->setJob($job);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function aggregateParticipantAssignedToRequest(Event $event)
    {
        $job = new Job(ParticipantAssignedToRequestAggregateCommand::NAME, [$event->getId()]);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function aggregateAvailableSlot(Event $event)
    {
        $job = new Job(AvailableSlotCalculatorCommand::NAME, [
            sprintf('--event=%s', $event->getId())
        ]);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function aggregateSheetAvailableSlot(Sheet $sheet)
    {
        $job = new Job(
            AvailableSlotCalculatorCommand::NAME,
            [
                sprintf('--sheet=%s', $sheet->getId()),
            ],
            true,
            Job::DEFAULT_QUEUE,
            Job::PRIORITY_LOW
        );
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function aggregatePhoneValidationStatus(Event $event)
    {
        $job = new Job(PhoneValidationStatusCalculatorCommand::NAME, [
            sprintf('--event=%s', $event->getId())
        ]);

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function generateMeetingSolutionAnalytic(Event $event)
    {
        $job = new Job(GenerateMeetingSolutionCommand::NAME, [$event->getId()]);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function indexSheetsByEvent(Event $event, bool $reset = false): void
    {
        $job = new Job(
            IndexSheetsByEventCommand::NAME,
            [
                $event->getId(),
                $reset ? IndexSheetsByEventCommand::RESET : IndexSheetsByEventCommand::NO_RESET,
                '--no-debug',
            ]
        );
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function exportOmzUser(Event $event, Admin $admin): void
    {
        $job = new Job(ExportUserCommand::NAME, [$event->getId(), $admin->getId()]);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function indexEventFromScratch(): void
    {
        $job = new Job(IndexFromScratchCommand::NAME, ['--no-debug']);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function sendEmailing(Event $event, array $sheetIds, $emailName, $sendEmailToTeam = false)
    {
        $job = new Job(
            SendEmailingCommand::NAME,
            [
                $event->getId(),
                $emailName,
                implode(',', $sheetIds),
                $sendEmailToTeam ? SendEmailingCommand::BOOL_YES : SendEmailingCommand::BOOL_NO,
            ]
        );
        $this->setJob($job);
    }

    /**
     * @param Event     $event
     * @param \DateTime $dateTime
     * @param Job|null  $job
     */
    public function scheduleVersionGeneration(Event $event, \DateTime $dateTime, Job $job = null)
    {
        if ($job !== null) {
            $job->setExecuteAfter($dateTime);

            $this->updateJob($job);

            return;
        }

        $job = new Job(
            GenerateVersionsCommand::NAME,
            [
                $event->getId(),
            ]
        );
        $job->addRelatedEntity($event);
        $job->setExecuteAfter($dateTime);

        $this->setJob($job);
    }
}
