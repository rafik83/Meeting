<?php

namespace Proximum\Vimeet\Domain\Nomenclature;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Nomenclature;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class RemoveAuthorizationChecker
{
    /** @var RegistrationTemplateRepositoryInterface */
    private $registrationTemplateRepository;

    /** @var SheetTemplateRepositoryInterface */
    private $sheetTemplateRepository;

    /** @var TemplateDataFactory */
    private $templateDataFactory;

    /** @var array of nomenclature indexed by event id */
    private $nomenclaturesUsedByEvent = [];

    /**
     * @param RegistrationTemplateRepositoryInterface $registrationTemplateRepository
     * @param SheetTemplateRepositoryInterface        $sheetTemplateRepository
     * @param TemplateDataFactory                     $templateDataFactory
     */
    public function __construct(
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository,
        SheetTemplateRepositoryInterface $sheetTemplateRepository,
        TemplateDataFactory $templateDataFactory
    ) {
        $this->registrationTemplateRepository = $registrationTemplateRepository;
        $this->sheetTemplateRepository = $sheetTemplateRepository;
        $this->templateDataFactory = $templateDataFactory;
    }

    /**
     * @param Event $event
     */
    public function preloadForEvent(Event $event)
    {
        $this->nomenclaturesUsedByEvent[$event->getId()] = [];

        $registrationTemplates = $this->registrationTemplateRepository->getTemplateForGivenEvent($event);
        $sheetTemplates = $this->sheetTemplateRepository->getTemplateForGivenEvent($event);
        $templates = array_merge($registrationTemplates, $sheetTemplates);

        foreach ($templates as $template) {
            $templateData = $this->templateDataFactory->createFromTemplate($template);

            $nomenclatureObjects = $templateData->getNomenclatureObjects();

            foreach ($nomenclatureObjects as $nomenclatureObject) {
                $nomenclature = $nomenclatureObject->getNomenclatureModel();

                $this->nomenclaturesUsedByEvent[$event->getId()][$nomenclature->getId()] = $nomenclature;
            }
        }
    }

    /**
     * @param Event $event
     *
     * @return array
     */
    public function getNomenclatureUsedOnEvent(Event $event): array
    {
        if (!isset($this->nomenclaturesUsedByEvent[$event->getId()])) {
            $this->preloadForEvent($event);
        }

        return $this->nomenclaturesUsedByEvent[$event->getId()];
    }

    /**
     * @param Nomenclature $nomenclature
     *
     * @return bool
     */
    public function canBeRemoved(Nomenclature $nomenclature): bool
    {
        if (null === $nomenclature->getEvent()) {
            return true;
        }

        $nomenclatures = $this->getNomenclatureUsedOnEvent($nomenclature->getEvent());

        return !in_array($nomenclature, $nomenclatures);
    }
}
