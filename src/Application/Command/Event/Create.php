<?php

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;

class Create extends AbstractEvent
{
    /** @var Admin */
    public $admin;

    /** @var bool */
    public $visio;

    /** @var null|Event */
    public $duplicatedFrom;

    /**
     * @param Admin      $admin
     * @param Event|null $event
     */
    public function __construct(Admin $admin, Event $event = null)
    {
        $this->admin = $admin;
        $this->timeZone = 'Europe/Paris';
        $this->country = 'FR';
        $this->visible = true;
        $this->welcomeEnabled = true;

        if (null !== $event) {
            $this->prefillData($event);
        }
    }

    /**
     * @param Event $event
     */
    public function prefillData(Event $event): void
    {
        $this->title = $event->getTitle();
        $this->locales = $event->getLocales();
        $this->fallback = $event->getLocaleFallback();
        $this->emailTeam = $event->getEmailTeam();
        $this->mode = $event->getMode();
        $this->visible = $event->isVisible();
        $this->country = $event->getCountry();
        $this->domain = $event->getDomain();
        $this->vat = $event->getVat();
        $this->currency = $event->getCurrency();
        $this->organiserName = $event->getOrganiserName();
        $this->invoicePrefix = $event->getInvoicePrefix();
        $this->timeZone = $event->getTimeZone();
        $this->duplicatedFrom = $event;
        $this->visio = $event->getConfiguration()->isVisio();
        $this->welcomeEnabled = $event->isWelcomeEnabled();
    }
}
