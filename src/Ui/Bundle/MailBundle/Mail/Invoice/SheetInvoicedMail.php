<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Invoice;

use Proximum\Vimeet\Application\Components\Mail\UserMail;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\View\Invoice\InvoiceUrlView;
use Proximum\Vimeet\Application\View\Participant\ParticipantInfoView;
use Proximum\Vimeet\Domain\Model\Sheet;

class SheetInvoicedMail extends UserMail
{
    /** @var string */
    protected $subject = 'mail.sheet.invoiced.subject';

    /** @var string */
    protected $template = 'MailBundle:Mail:Invoice/sheetInvoiced.html.twig';

    /** @var string */
    protected $messageId = Events::SHEET_INVOICED;

    /** @var bool */
    protected $sendToEmailTeam = true;

    /** @var Sheet */
    private $sheet;

    /** @var InvoiceUrlView[] */
    private $invoiceUrlViews;

    /** @var string */
    private $ordersUrl;

    /**
     * @param Sheet               $sheet
     * @param string              $sender
     * @param array               $receivers
     * @param ParticipantInfoView $participantInfoView
     * @param string              $locale
     * @param InvoiceUrlView[]    $invoiceUrlViews
     * @param string              $ordersUrl
     *
     * @throws \Exception
     */
    public function __construct(
        Sheet $sheet,
        $sender,
        array $receivers,
        $participantInfoView,
        $locale,
        array $invoiceUrlViews,
        $ordersUrl
    ) {
        $firstReceiver = reset($receivers);

        if (false === $firstReceiver) {
            throw new \Exception('No receiver given');
        }

        parent::__construct($sender, $firstReceiver, $locale, $sheet->getEvent(), $participantInfoView);

        $this->sheet           = $sheet;
        $this->invoiceUrlViews = $invoiceUrlViews;
        $this->ordersUrl       = $ordersUrl;

        foreach ($receivers as $receiver) {
            $this->addReceiver($receiver);
        }
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
            '%event%' => $this->getEvent()->getTitle(),
        ];
    }

    /**
     * @return string
     */
    public function getOrdersUrl()
    {
        return $this->ordersUrl;
    }

    /**
     * @return InvoiceUrlView[]
     */
    public function getInvoiceUrlViews()
    {
        return $this->invoiceUrlViews;
    }
}
