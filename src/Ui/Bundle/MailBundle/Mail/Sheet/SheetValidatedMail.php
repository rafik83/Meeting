<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Sheet;

use Proximum\Vimeet\Application\Components\Mail\Mail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetValidatedMail extends Mail
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
     * SheetValidatedMail constructor.
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
     * {@inheritdoc}
     */
    public function getSubjectParameters()
    {
        return [
            '%event%'             => $this->getEvent()->getTitle(),
            '%participationType%' => $this->getSheet()->getType()->getTitle($this->getLocale()),
        ];
    }
}
