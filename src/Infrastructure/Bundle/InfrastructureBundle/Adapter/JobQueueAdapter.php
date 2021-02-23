<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter;

use DateTime;
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
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\Message\Job;
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
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Command\Sheet\Export\ExportSheetCommand;
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
        $job = new Job(SendCampaignCommand::NAME, ['id' => $campaign->getId()]);
        // $job->addRelatedEntity($campaign);
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
                '--sheetIdsExtraData' => $extraData->getId(),
                '--orderBy' => $orderBy,
                '--emailToNotify', $emailToNotify,
                '--locale' => $locale,
                '--printOption' => $printOption,
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
            '--eventId' => $event->getId(),
            '--extraDataWithParticipantIds' => $extraData->getId(),
            '--adminId' => $admin->getId(),
            '--locale' => $locale,
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
            '--sheetIds' => implode(',', $sheetIds),
            '--eventId' => $event->getId(),
            '--emailToNotify' => $emailToNotify,
            '--locale' => $locale,
            '--orderBy' => $orderBy,
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
                '--sheetIds' => implode(',', $sheetIds),
                '--eventId' => $event->getId(),
                '--emailToNotify' => $emailToNotify,
                '--locale' => $locale,
            ]
        );

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function exportOrdersForEvent(Event $event, Admin $admin, $locale): void
    {
        $job = new Job(ExportOrderCommand::NAME, [
            'event' => $event->getId(),
            'emailToNotify' => $admin->getEmail(),
            'locale' => $locale,
        ]);

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function exportProductsForEvent(Event $event, Admin $admin, string $locale): void
    {
        $job = new Job(ExportCommand::NAME, [
            'event' => $event->getId(),
            'emailToNotify' => $admin->getEmail(),
            'locale' => $locale
        ]);

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
                'eventId' => $event->getId(),
                'admin_email' => $admin->getEmail(),
                'locale' => $locale,
                'solutionType' => $solutionType,
                'lockMeetingRequest' => true === $lockMeetingRequest
                    ? ExportPlannerCommand::LOCK_MEETING_REQUEST
                    : ExportPlannerCommand::DONT_LOCK_MEETING_REQUEST,
                'mode' => true === $isModeAuto
                    ? ExportPlannerCommand::MODE_AUTO
                    : ExportPlannerCommand::MODE_MANUAL,
                'plannerJob' => null !== $plannerJob ? $plannerJob->getId() : null,
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
            'file' => $file->getId(),
            'event' => $event->getId(),
            'admin_email' => $admin->getEmail(),
            'locale' => $locale,
            'plannerJobId' => $plannerJob instanceof PlannerJob ? $plannerJob->getId() : null,
        ]);

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function indexSheetsBySheetTemplate(SheetTemplate $sheetTemplate): void
    {
        $command = IndexSheetsBySheetTemplateCommand::NAME;
        $arguments = ['sheetTemplateId' => $sheetTemplate->getId()];

        $job = new Job($command, $arguments);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function indexSheetsByRegistrationTemplate(RegistrationTemplate $registrationTemplate): void
    {
        $command = IndexSheetsByRegistrationTemplateCommand::NAME;
        $arguments = ['registrationTemplateId' => $registrationTemplate->getId()];

        $job = new Job($command, $arguments);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function indexSheetsByTypes(array $typeIds)
    {
        $job = new Job(IndexSheetsByTypesCommand::NAME, ['typeIds' => implode(',', $typeIds)]);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function indexInCatalogSheetsByEvent(Event $event)
    {
        $job = new Job(IndexInCatalogSheetsByEventCommand::NAME, ['eventId' => $event->getId()]);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function indexSheets(array $sheetIds)
    {
        $job = new Job(
            IndexSheetsCommand::NAME,
            ['sheetIds' => implode(',', $sheetIds)],
        );
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function aggregateEventUsersFullUnavailability(Event $event, $onlyInCatalog = false): void
    {
        $command = UsersFullUnavailabilityByEventAggregateCommand::NAME;
        $arguments =  [
            'eventId' => $event->getId(),
            'onlyCatalog' => $onlyInCatalog,
        ];

        $job = new Job($command, $arguments);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function aggregateUsersFullUnavailability(Event $event, array $users): void
    {
        if (!empty($users)) {
            $job = new Job(UsersFullUnavailabilityAggregateCommand::NAME, [
                'eventId' => $event->getId(),
                'userIds' => implode(',', array_map(function (User $user) {
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
        $job = new Job(ParticipantAssignedToRequestAggregateCommand::NAME, ['event' => $event->getId()]);
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function aggregateAvailableSlot(Event $event): void
    {
        $command = AvailableSlotCalculatorCommand::NAME;
        $arguments = [
            '--event' => $event->getId(),
        ];

        $job = new Job($command, $arguments);
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
                '--sheet' => $sheet->getId(),
            ]
        );
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function aggregatePhoneValidationStatus(Event $event): void
    {
        $job = new Job(PhoneValidationStatusCalculatorCommand::NAME, [
            '--event' => $event->getId(),
        ]);

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function generateMeetingSolutionAnalytic(Event $event): void
    {
        $job = new Job(GenerateMeetingSolutionCommand::NAME, ['eventId' => $event->getId()]);
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
                'eventId' => $event->getId(),
                'reset' => $reset ? IndexSheetsByEventCommand::RESET : IndexSheetsByEventCommand::NO_RESET,
            ]
        );
        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function exportOmzUser(Event $event, Admin $admin): void
    {
        $job = new Job(ExportUserCommand::NAME, [
            'event' => $event->getId(),
            'admin' => $admin->getId()
        ]);

        $this->setJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function indexEventFromScratch(): void
    {
        $job = new Job(IndexFromScratchCommand::NAME, []);
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
                'eventId' => $event->getId(),
                'emailingId' => $emailName,
                'sheetIds' => implode(',', $sheetIds),
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
                'eventId' => $event->getId(),
            ]
        );
        // $job->addRelatedEntity($event);
        $job->setExecuteAfter($dateTime);

        $this->setJob($job);
    }

    public function exportUploadedObjectsBySheets(Event $event, Admin $admin, Event\ExtraData $extraData): void
    {
        $job = new Job(ExportUploadedObjectsBySheetsCommand::NAME, [
            'eventId' => $event->getId(),
            'extraDataId' => $extraData->getId(),
            'adminId' => $admin->getId(),
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
            'eventId' => $event->getId(),
            'formTemplateId' => $formTemplate->getId(),
            'extraDataId' => $extraData->getId(),
            'adminId' => $admin->getId(),
            'locale' => $locale,
        ]);

        $this->setJob($job);
    }

    public function exportRoomingList(Event $event, Admin $admin, string $locale): void
    {
        $job = new Job(ExportRoomingListCommand::NAME, [
            'eventId' => $event->getId(),
            'adminId' => $admin->getId(),
            'locale' => $locale
        ]);

        $this->setJob($job);
    }

    public function downloadTranslations(?string $emailToNotify = null, ?string $locale = null): void
    {
        $arguments = [];
        if ($emailToNotify && $locale) {
            $arguments['emailToNotify'] = $emailToNotify;
            $arguments['locale'] = $locale;
        }
        $job = new Job(UpdateTranslationsCommand::NAME, $arguments);

        $this->setJob($job);
    }

    public function scheduleUpdateTranslations(?string $emailToNotify = null, ?string $locale = null): void
    {
        $arguments = [];
        if ($emailToNotify && $locale) {
            $arguments['emailToNotify'] = $emailToNotify;
            $arguments['locale'] = $locale;
        }
        $job = new Job(
            ScheduleUpdateTranslationsCommand::NAME,
            $arguments
        );

        $this->setJob($job);
    }

    public function exportHappeningParticipants(Event $event, Admin $admin, string $locale): void
    {
        $this->setJob(new Job(ExportParticipantsCommand::NAME, [
            'event' => $event->getId(),
            'admin' => $admin->getId(),
            'locale' => $locale
        ]));
    }

    public function zipRecordArchive(
        Happening $happening,
        bool $forceRegeneration = false,
        ?Admin $admin = null,
        ?string $locale = null
    ): void {
        $arguments = [
            'happening' => $happening->getId(),
            'force-regeneration' => $forceRegeneration ? 'force' : 'no-force'
        ];

        if ($admin instanceof Admin) {
            $arguments['admin'] = $admin->getId();

            if ($locale) {
                $arguments['locale'] = $locale;
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
            ['happening' => $happening->getId()]
        );

        $job->setExecuteAfter($dueDate);

        $this->setJob($job);
    }

    public function exportSheet(
        Event $event,
        Admin $admin,
        Event\ExtraData $extraData,
        string $locale,
        bool $displayNomenclatureIds
    ): void {
        $job = new Job(
            ExportSheetCommand::NAME,
            [
                '--eventId' => $event->getId(),
                '--extraDataWithSheetIds' => $extraData->getId(),
                '--adminId' => $admin->getId(),
                '--locale' => $locale,
                '--displayNomenclatureIds' => $displayNomenclatureIds ? 'true' : 'false',
            ]
        );

        $this->setJob($job);
    }
}
