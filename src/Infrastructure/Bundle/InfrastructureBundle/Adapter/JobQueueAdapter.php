<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter;

use DateTime;
use JMS\JobQueueBundle\Entity\Job;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\PlannerJob;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
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
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\ExportUploadedObjectsBySheetsCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Happening\ExportParticipantsCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Happening\Webinar\Record\CreateZipRecordArchiveCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Happening\Webinar\Record\ForceZipRecordArchiveCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\IndexSheetsCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Invoice\GenerateInvoiceCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Invoice\PrintInvoicesCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\OMZ\ExportUserCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Order\ExportOrderCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Participant\Export\ExportParticipantCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Planner\ExportPlannerCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Planner\ImportPlannerCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Planning\GeneratePlanningCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Product\ExportCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Rooming\Export\ExportRoomingListCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\SendCampaignCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\SendEmailingCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Sheet\Index\IndexInCatalogSheetsByEventCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Sheet\Index\IndexSheetsByRegistrationTemplateCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Sheet\Index\IndexSheetsBySheetTemplateCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Sheet\Index\IndexSheetsByTypesCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Sheet\PrintPdfCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Template\Form\ExportFormTemplateDataByUsersCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Translation\ScheduleUpdateTranslationsCommand;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Translation\UpdateTranslationsCommand;

class JobQueueAdapter extends AbstractJobQueueAdapter implements JobQueueInterface
{
    /**
     * {@inheritdoc}
     */
    public function sendCampaign(Campaign $campaign): void
    {
        $job = new Job(SendCampaignCommand::NAME, [$campaign->getId()]);
        $job->addRelatedEntity($campaign);
        $this->setJob($job);
    }

    public function printPlanning(
        Event\ExtraData $extraData,
        string $orderBy,
        string $emailToNotify,
        string $locale,
        string $printOption
    ): void {
        $job = new Job(
            GeneratePlanningCommand::NAME,
            [
                sprintf('--sheetIdsExtraData=%s', $extraData->getId()),
                sprintf('--orderBy=%s', $orderBy),
                sprintf('--emailToNotify=%s', $emailToNotify),
                sprintf('--locale=%s', $locale),
                sprintf('--printOption=%s', $printOption),
            ]
        );

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function exportParticipantsForEvent(
        Event $event,
        Admin $admin,
        string $locale,
        Event\ExtraData $extraData
    ): void {
        $job = new Job(ExportParticipantCommand::NAME, [
            sprintf('--eventId=%s', $event->getId()),
            sprintf('--extraDataWithParticipantIds=%s', $extraData->getId()),
            sprintf('--adminId=%s', $admin->getId()),
            sprintf('--locale=%s', $locale),
        ]);

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
    ): void {
        $job = new Job(PrintPdfCommand::NAME, [
            sprintf('--sheetIds=%s', implode(',', $sheetIds)),
            sprintf('--eventId=%s', $event->getId()),
            sprintf('--emailToNotify=%s', $emailToNotify),
            sprintf('--locale=%s', $locale),
            sprintf('--orderBy=%s', $orderBy),
        ]);

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function generateInvoice(Event $event, array $sheetIds, Admin $admin): void
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
    public function printInvoicesPdf(Event $event, array $sheetIds, string $emailToNotify, string $locale): void
    {
        $job = new Job(
            PrintInvoicesCommand::NAME,
            [
                sprintf('--sheetIds=%s', implode(',', $sheetIds)),
                sprintf('--eventId=%s', $event->getId()),
                sprintf('--emailToNotify=%s', $emailToNotify),
                sprintf('--locale=%s', $locale),
            ]
        );

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function exportOrdersForEvent(Event $event, Admin $admin, $locale): void
    {
        $job = new Job(ExportOrderCommand::NAME, [$event->getId(), $admin->getEmail(), $locale]);

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function exportProductsForEvent(Event $event, Admin $admin, string $locale): void
    {
        $job = new Job(ExportCommand::NAME, [$event->getId(), $admin->getEmail(), $locale]);

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
    ): void {
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
                null !== $plannerJob ? $plannerJob->getId() : null,
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
    ): void {
        $job = new Job(ImportPlannerCommand::NAME, [
            $file->getId(),
            $event->getId(),
            $admin->getEmail(),
            $locale,
            $plannerJob instanceof PlannerJob ? $plannerJob->getId() : null,
        ]);

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function indexSheetsBySheetTemplate(SheetTemplate $sheetTemplate): void
    {
        $command = IndexSheetsBySheetTemplateCommand::NAME;
        $args = [$sheetTemplate->getId()];

        $job = new Job($command, $args);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function indexSheetsByRegistrationTemplate(RegistrationTemplate $registrationTemplate): void
    {
        $command = IndexSheetsByRegistrationTemplateCommand::NAME;
        $args = [$registrationTemplate->getId()];

        $job = new Job($command, $args);
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
    public function aggregateEventUsersFullUnavailability(Event $event, $onlyInCatalog = false): void
    {
        $command = UsersFullUnavailabilityByEventAggregateCommand::NAME;
        $args =  [
            $event->getId(),
            $onlyInCatalog,
        ];

        $job = new Job($command, $args);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function aggregateUsersFullUnavailability(Event $event, array $users): void
    {
        if (!empty($users)) {
            $job = new Job(UsersFullUnavailabilityAggregateCommand::NAME, [
                $event->getId(),
                implode(',', array_map(function (User $user) {
                    return $user->getId();
                }, $users)),
            ]);
            $this->setJob($job);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function aggregateParticipantAssignedToRequest(Event $event): void
    {
        $job = new Job(ParticipantAssignedToRequestAggregateCommand::NAME, [$event->getId()]);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function aggregateAvailableSlot(Event $event): void
    {
        $command = AvailableSlotCalculatorCommand::NAME;
        $args = [
            sprintf('--event=%s', $event->getId()),
        ];

        $job = new Job($command, $args);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function aggregateSheetAvailableSlot(Sheet $sheet): void
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
    public function aggregatePhoneValidationStatus(Event $event): void
    {
        $job = new Job(PhoneValidationStatusCalculatorCommand::NAME, [
            sprintf('--event=%s', $event->getId()),
        ]);

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function generateMeetingSolutionAnalytic(Event $event): void
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
    public function sendEmailing(Event $event, array $sheetIds, $emailName): void
    {
        $job = new Job(
            SendEmailingCommand::NAME,
            [
                $event->getId(),
                $emailName,
                implode(',', $sheetIds),
            ]
        );
        $this->setJob($job);
    }

    /**
     * @param Event     $event
     * @param \DateTime $dateTime
     * @param Job|null  $job
     */
    public function scheduleVersionGeneration(Event $event, \DateTime $dateTime, Job $job = null): void
    {
        if (null !== $job) {
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

    public function exportUploadedObjectsBySheets(Event $event, Admin $admin, Event\ExtraData $extraData): void
    {
        $job = new Job(ExportUploadedObjectsBySheetsCommand::NAME, [
            $event->getId(),
            $extraData->getId(),
            $admin->getId(),
        ]);

        $this->setJob($job);
    }

    public function exportFormTemplateDataByUsers(
        Event $event,
        FormTemplate $formTemplate,
        Admin $admin,
        string $locale,
        Event\ExtraData $extraData
    ): void {
        $job = new Job(ExportFormTemplateDataByUsersCommand::NAME, [
            $event->getId(),
            $formTemplate->getId(),
            $extraData->getId(),
            $admin->getId(),
            $locale
        ]);

        $this->setJob($job);
    }

    public function exportRoomingList(Event $event, Admin $admin, string $locale): void
    {
        $job = new Job(ExportRoomingListCommand::NAME, [
            $event->getId(),
            $admin->getId(),
            $locale
        ]);

        $this->setJob($job);
    }

    public function downloadTranslations(?string $emailToNotify = null, ?string $locale = null): void
    {
        $job = new Job(UpdateTranslationsCommand::NAME, $emailToNotify && $locale ? [$emailToNotify, $locale] : []);

        $this->setJob($job);
    }

    public function scheduleUpdateTranslations(?string $emailToNotify = null, ?string $locale = null): void
    {
        $job = new Job(
            ScheduleUpdateTranslationsCommand::NAME,
            $emailToNotify && $locale ? [$emailToNotify, $locale] : []
        );

        $this->setJob($job);
    }

    public function exportHappeningParticipants(Event $event, Admin $admin, string $locale): void
    {
        $this->setJob(new Job(ExportParticipantsCommand::NAME, [$event->getId(), $admin->getId(), $locale]));
    }

    public function zipRecordArchive(
        Happening $happening,
        bool $forceRegeneration = false,
        ?Admin $admin = null,
        ?string $locale = null
    ): void {
        $arguments = [
            $happening->getId(),
            $forceRegeneration ? 'force' : 'no-force'
        ];

        if ($admin instanceof Admin) {
            $arguments[] = $admin->getId();

            if ($locale) {
                $arguments[] = $locale;
            }
        }
        $job = new Job(
            ForceZipRecordArchiveCommand::NAME,
            $arguments
        );

        $this->setJob($job);
    }

    public function planDownloadRecordArchive(
        Happening $happening,
        DateTime $dueDate
    ): void {
        $job = new Job(
            CreateZipRecordArchiveCommand::NAME,
            [$happening->getId()]
        );

        $job->setExecuteAfter($dueDate);

        $this->setJob($job);
    }
}
