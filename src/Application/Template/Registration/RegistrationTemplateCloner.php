<?php

namespace Proximum\Vimeet\Application\Template\Registration;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Template\Registration\RegistrationTemplateUpdatedEvent;
use Proximum\Vimeet\Application\Nomenclature\NomenclatureCloner;
use Proximum\Vimeet\Application\Template\TemplateCloner;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RegistrationTemplateCloner extends TemplateCloner
{
    /**
     * @var RegistrationTemplateRepositoryInterface
     */
    private $registrationTemplateRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * RegistrationTemplateCloner constructor.
     *
     * @param RegistrationTemplateRepositoryInterface $registrationTemplateRepository
     * @param TemplateDataFactory                     $templateDataFactory
     * @param NomenclatureCloner                      $nomenclatureCloner
     * @param EventDispatcherInterface                $eventDispatcher
     * @param \DateTimeInterface                      $dateTime
     */
    public function __construct(
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository,
        TemplateDataFactory $templateDataFactory,
        NomenclatureCloner $nomenclatureCloner,
        EventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        parent::__construct($templateDataFactory, $nomenclatureCloner);

        $this->registrationTemplateRepository = $registrationTemplateRepository;
        $this->eventDispatcher                = $eventDispatcher;
        $this->dateTime                       = $dateTime;
    }

    /**
     * @param RegistrationTemplate $template
     * @param Event                $event
     * @param string               $title
     *
     * @return RegistrationTemplate
     */
    public function duplicate(RegistrationTemplate $template, Event $event, $title)
    {
        $clone = new RegistrationTemplate(
            $title,
            $template->getValue(),
            $template->getLocales(),
            $template->getFallback(),
            $this->dateTime,
            $template->getEvent()
        );

        if ($event !== $template->getEvent()) {
            $this->switchEvent($event, $clone);
        }

        $this->registrationTemplateRepository->add($clone);

        $this->eventDispatcher->dispatch(
            Events::REGISTRATION_TEMPLATE_UPDATED,
            new RegistrationTemplateUpdatedEvent($event)
        );

        return $clone;
    }
}
