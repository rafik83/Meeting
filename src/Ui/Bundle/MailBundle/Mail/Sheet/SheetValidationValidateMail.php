<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;

use Proximum\Vimeet\Application\Components\Mail\Mail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetValidationValidateMail extends Mail
{
    /**
     * @var string
     */
    protected $subject = 'mail.sheet.validation.validate.subject';

    /**
     * @var string
     */
    protected $template = 'MailBundle:Mail:Sheet/sheetValidationValidate.html.twig';

    /**
     * @var string
     */
    protected $messageId = Events::SHEET_VALIDATION_VALIDATE;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = true;

    /**
     * SheetValidatedValidate constructor.
     *
     * @param Sheet  $sheet
     * @param string $sender
     * @param string $receiver
     * @param string $locale
     */
    public function __construct(Sheet $sheet, $sender, $receiver, $locale)
    {
        parent::__construct($sender, $receiver, $locale, null, null, $sheet->getEvent());

        $this->sheet = $sheet;
    }

    /**
     * Get sheet
     *
     * @return Sheet
     */
    public function getSheet()
    {
        return $this->sheet;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->sheet->getEvent();
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->sheet->getType()->getTitle($this->locale);
    }
}
