<?php

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BillingConfiguration
{
    /**
     * @var Event
     */
    public $event;

    /**
     * @var string
     */
    public $legalInfo;

    /**
     * @var array
     */
    public $translations = [];

    /**
     * @var UploadedFile
     */
    public $invoiceLogo;

    /**
     * BillingConfiguration constructor.
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;

        $this->legalInfo = $event->getConfiguration()->getLegalInfo();

        foreach ($event->getLocales() as $locale) {
            $this->translations[$locale] = [
                'bankInfo'         => $event->getBankInfo($locale),
                'billingAddress'   => $event->getBillingAddress($locale),
                'paymentCondition' => $event->getPaymentCondition($locale),
                'paymentFooter'    => $event->getPaymentFooter($locale),
            ];
        }
    }
}
