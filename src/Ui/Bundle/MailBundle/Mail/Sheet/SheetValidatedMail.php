<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\View\Mail\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetValidatedMail extends UserMail
{
    /**
     * @var string
     */
    protected $subject = 'mail.sheet.validated.subject';

    /**
     * @var string
     */
    protected $template = 'MailBundle:Mail:Sheet/sheetValidated.html.twig';

    /**
     * @var string
     */
    protected $messageId = Events::SHEET_VALIDATED;

    /**
     * @var Sheet
     */
    private $sheet;

    /**
     * @var bool
     */
    protected $sendToEmailTeam = true;

    /**
     * SheetValidatedMail constructor.
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

    /**
     * {@inheritdoc}
     */
    public function getSubjectParameters()
    {
        return [
            '%event%'             => $this->getEvent()->getTitle(),
            '%participationType%' => $this->getParticipantType(),
        ];
    }
}
