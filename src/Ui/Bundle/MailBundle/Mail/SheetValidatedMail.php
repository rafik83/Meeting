<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail;

use Proximum\Vimeet\Application\Components\Mail\Mail;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetValidatedMail extends Mail
{
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
     * @param string $template
     * @param string $messageId
     * @param string $locale
     */
    public function __construct(Sheet $sheet, $sender, $receiver, $template, $messageId, $locale)
    {
        $this->sheet = $sheet;

        parent::__construct($sender, $receiver, $template, $messageId, $locale);
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
