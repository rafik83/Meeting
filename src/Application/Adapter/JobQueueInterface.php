<?php

namespace Proximum\Vimeet\Application\Adapter;

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

interface JobQueueInterface
{
    /**
     * @param Campaign $campaign
     */
    public function sendCampaign(Campaign $campaign);

    /**
     * @param Event  $event
     * @param int[]  $sheetIds
     * @param string $emailName
     */
    public function sendEmailing(Event $event, array $sheetIds, $emailName);

    public function printPlanning(
        Event\ExtraData $extraData,
        string $orderBy,
        string $emailToNotify,
        string $locale,
        string $printOption
    ): void;

    /**
     * @param Event  $event
     * @param array  $sheetIds
     * @param string $emailToNotify
     * @param string $locale
     * @param string $orderBy
     */
    public function printSheetsPdf(
        Event $event,
        array $sheetIds,
        string $emailToNotify,
        string $locale,
        string $orderBy
    );

    /**
     * @param Event $event
     * @param int[] $sheetIds
     * @param Admin $admin
     */
    public function generateInvoice(Event $event, array $sheetIds, Admin $admin);

    /**
     * @param Event  $event
     * @param int[]  $sheetIds
     * @param string $emailToNotify
     * @param string $locale
     */
    public function printInvoicesPdf(Event $event, array $sheetIds, string $emailToNotify, string $locale): void;

    /**
     * @param Event  $event
     * @param Admin  $admin
     * @param string $locale
     */
    public function exportOrdersForEvent(Event $event, Admin $admin, $locale);

    public function exportProductsForEvent(Event $event, Admin $admin, string $locale): void;

    /**
     * @param Event           $event
     * @param Admin           $admin
     * @param string          $locale
     * @param bool            $lockMeetingRequest
     * @param string          $solutionType
     * @param bool            $isModeAuto
     * @param null|PlannerJob $plannerJob
     */
    public function exportPlannerForEvent(
        Event $event,
        Admin $admin,
        string $locale,
        bool $lockMeetingRequest,
        string $solutionType,
        bool $isModeAuto,
        ?PlannerJob $plannerJob
    );

    /**
     * @param File            $file
     * @param Event           $event
     * @param Admin           $admin
     * @param string          $locale
     * @param null|PlannerJob $plannerJob
     */
    public function importPlannerForEvent(
        File $file,
        Event $event,
        Admin $admin,
        $locale,
        ?PlannerJob $plannerJob = null
    );

    /**
     * @param SheetTemplate $sheetTemplate
     */
    public function indexSheetsBySheetTemplate(SheetTemplate $sheetTemplate);

    /**
     * @param RegistrationTemplate $registrationTemplate
     */
    public function indexSheetsByRegistrationTemplate(RegistrationTemplate $registrationTemplate);

    /**
     * @param int[] $typeIds
     */
    public function indexSheetsByTypes(array $typeIds);

    /**
     * @param Event $event
     */
    public function indexInCatalogSheetsByEvent(Event $event);

    /**
     * @param int[] $sheetIds
     */
    public function indexSheets(array $sheetIds);

    /**
     * @param Event $event
     * @param bool  $onlyInCatalog
     */
    public function aggregateEventUsersFullUnavailability(Event $event, $onlyInCatalog = false);

    /**
     * @param Event  $event
     * @param User[] $users
     */
    public function aggregateUsersFullUnavailability(Event $event, array $users);

    /**
     * @param Event $event
     */
    public function aggregateParticipantAssignedToRequest(Event $event);

    /**
     * @param Event $event
     */
    public function aggregateAvailableSlot(Event $event);

    /**
     * @param Sheet $sheet
     */
    public function aggregateSheetAvailableSlot(Sheet $sheet);

    /**
     * @param Event $event
     */
    public function aggregatePhoneValidationStatus(Event $event);

    /**
     * @param $event
     */
    public function generateMeetingSolutionAnalytic(Event $event);

    /**
     * This method re-index all the sheets of a given event
     * If the reset parameter is true, it removes all the entries of the given event first
     *
     * @param Event $event
     * @param bool  $reset
     */
    public function indexSheetsByEvent(Event $event, bool $reset = false): void;

    /**
     * Export the users' data for the OMZ
     *
     * @param Event $event
     * @param Admin $admin
     */
    public function exportOmzUser(Event $event, Admin $admin): void;

    /**
     * This method re-index all the events
     * It does reset ES
     */
    public function indexEventFromScratch(): void;

    /**
     * @param Event           $event
     * @param Admin           $admin
     * @param string          $locale
     * @param Event\ExtraData $extraData
     */
    public function exportParticipantsForEvent(Event $event, Admin $admin, string $locale, Event\ExtraData $extraData): void;

    public function exportUploadedObjectsBySheets(Event $event, Admin $admin, Event\ExtraData $extraData): void;

    public function exportFormTemplateDataByUsers(
        Event $event,
        FormTemplate $formTemplate,
        Admin $admin,
        string $locale,
        Event\ExtraData $extraData
    ): void;

    public function exportRoomingList(
        Event $event,
        Admin $admin,
        string $locale
    ): void;

    public function downloadTranslations(?string $emailToNotify = null, ?string $locale = null): void;

    public function scheduleUpdateTranslations(?string $emailToNotify = null, ?string $locale = null): void;

    public function exportHappeningParticipants(Event $event, Admin $admin, string $locale): void;

    public function zipRecordArchive(
        Happening $happening,
        bool $forceRegeneration = false,
        ?Admin $admin = null,
        ?string $locale = null
    ): void;
}
