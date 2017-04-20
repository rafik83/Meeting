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
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;
use Proximum\Vimeet\Domain\Model\Type;

interface JobQueueInterface
{
    /**
     * @param Campaign $campaign
     */
    public function sendCampaign(Campaign $campaign);

    /**
     * @param Event  $event
     * @param Int[]  $sheetIds
     * @param string $emailId
     */
    public function sendEmailing(Event $event, array $sheetIds, $emailId);

    /**
     * @param Type[] $types
     * @param string $orderBy
     * @param string $emailToNotify
     * @param string $locale
     */
    public function printPlanning(array $types, $orderBy, $emailToNotify, $locale);

    /**
     * @param int[] $sheetIds
     * @param Admin $admin
     */
    public function generateInvoice(array $sheetIds, Admin $admin);

    /**
     * @param Event  $event
     * @param Admin  $admin
     * @param string $locale
     */
    public function exportOrdersForEvent(Event $event, Admin $admin, $locale);

    /**
     * @param Event  $event
     * @param Admin  $admin
     * @param string $locale
     * @param string $lockMeetingRequest
     * @param string $solutionType
     */
    public function exportPlannerForEvent(Event $event, Admin $admin, $locale, $lockMeetingRequest, $solutionType);

    /**
     * @param File   $file
     * @param Event  $event
     * @param Admin  $admin
     * @param string $locale
     */
    public function importPlannerForEvent(File $file, Event $event, Admin $admin, $locale);

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
}
