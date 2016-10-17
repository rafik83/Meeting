<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;

use DateTimeInterface;
use Proximum\Vimeet\Application\Components\Mail\Mail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SheetSubmittedMail extends Mail
{
    /**
     * @var string
     */
    protected $subject = 'mail.sheet.submitted.subject';

    /**
     * @var string
     */
    protected $template = 'MailBundle:Mail:Sheet/sheetSubmitted.html.twig';

    /**
     * @var string
     */
    protected $messageId = Events::SHEET_VALIDATION_PENDING;

    /**
     * @var Admin
     */
    private $admin;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var DateTimeInterface
     */
    private $datetime;

    /**
     * @var string
     */
    private $sheetOrganization;

    /**
     * @param Event             $event
     * @param string            $sender
     * @param string            $receiver
     * @param string            $locale
     * @param Sheet             $sheet
     * @param Admin             $admin
     * @param DateTimeInterface $datetime
     * @param string            $sheetOrganization
     */
    public function __construct(
        Event $event,
        $sender,
        $receiver,
        $locale,
        Sheet $sheet,
        Admin $admin,
        DateTimeInterface $datetime,
        $sheetOrganization
    ) {
        parent::__construct($sender, $receiver, $locale, null, null, $event);

        $this->admin             = $admin;
        $this->sheet             = $sheet;
        $this->datetime          = $datetime;
        $this->sheetOrganization = $sheetOrganization;

        // set sheet organization for mail subject parameters
        $this->subjectParameters['%sheetOrganization%'] = $sheetOrganization;
    }

    /**
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return Admin
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->sheet->getOwner();
    }

    /**
     * @return DateTimeInterface
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * @return mixed
     */
    public function getSheetOrganization()
    {
        return $this->sheetOrganization;
    }
}
