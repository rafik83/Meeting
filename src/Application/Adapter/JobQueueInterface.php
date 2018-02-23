<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

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

interface JobQueueInterface
{
    /**
     * @param Campaign $campaign
     */
    public function sendCampaign(Campaign $campaign);

    /**
     * @param Event  $event
     * @param Int[]  $sheetIds
     * @param string $emailName
     * @param bool   $sendEmailToTeam
     */
    public function sendEmailing(Event $event, array $sheetIds, $emailName, $sendEmailToTeam = false);

    /**
     * @param Type[] $types
     * @param string $orderBy
     * @param string $emailToNotify
     * @param string $locale
     */
    public function printPlanning(array $types, string $orderBy, $emailToNotify, $locale) :void;

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
     * @param Admin  $admin
     * @param string $locale
     */
    public function exportOrdersForEvent(Event $event, Admin $admin, $locale);

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
     * @param Int[] $sheetIds
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
     * It does not reset ES
     *
     * @param $event
     */
    public function indexSheetsByEvent(Event $event): void;
}
