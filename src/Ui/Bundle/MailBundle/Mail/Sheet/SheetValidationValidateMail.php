<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetValidationValidateMail extends UserMail
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
     * @param Sheet               $sheet
     * @param string              $sender
     * @param string              $receiver
     * @param string              $locale
     * @param ParticipantInfoView $participantInfoView
     */
    public function __construct(
        Sheet $sheet,
        $sender,
        $receiver,
        $locale,
        ParticipantInfoView $participantInfoView
    ) {
        parent::__construct($sender, $receiver, $locale, $sheet->getEvent(), $participantInfoView);

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
}
