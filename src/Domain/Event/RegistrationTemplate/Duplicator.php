<?php

namespace Proximum\Vimeet\Domain\Event\RegistrationTemplate;

use Proximum\Vimeet\Application\Template\Registration\RegistrationTemplateCloner;
use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;

class Duplicator
{
    /** @var RegistrationTemplateRepositoryInterface */
    private $registrationTemplateRepository;

    /** @var RegistrationTemplateCloner */
    private $registrationTemplateCloner;

    /**
     * @param RegistrationTemplateRepositoryInterface $registrationTemplateRepository
     * @param RegistrationTemplateCloner              $registrationTemplateCloner
     */
    public function __construct(
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository,
        RegistrationTemplateCloner $registrationTemplateCloner
    ) {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
        $this->registrationTemplateCloner     = $registrationTemplateCloner;
    }

    /**
     * @param Event                 $event
     * @param DuplicatorDataStorage $duplicatorDataStorage
     *
     * @return DuplicatorDataStorage
     */
    public function duplicate(Event $event, DuplicatorDataStorage $duplicatorDataStorage): DuplicatorDataStorage
    {
        $registrationTemplates = $this
            ->registrationTemplateRepository
            ->getTemplateForGivenEvent($event->getDuplicatedFrom());

        foreach ($registrationTemplates as $registrationTemplate) {
            $clonedTemplate = $this->registrationTemplateCloner->duplicate(
                $registrationTemplate,
                $event,
                $registrationTemplate->getTitle()
            );

            $duplicatorDataStorage->registrationTemplates[$registrationTemplate->getId()] = $clonedTemplate;
        }

        return $duplicatorDataStorage;
    }
}
