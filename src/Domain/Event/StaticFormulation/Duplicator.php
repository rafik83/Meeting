<?php

namespace Proximum\Vimeet\Domain\Event\StaticFormulation;

use Proximum\Vimeet\Domain\Event\DuplicatorDataStorage;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Repository\StaticFormulation\StaticFormulationRepositoryInterface;

class Duplicator
{
    /** @var StaticFormulationRepositoryInterface */
    private $staticFormulationRepository;

    public function __construct(
        StaticFormulationRepositoryInterface $staticFormulationRepository
    ) {
        $this->staticFormulationRepository = $staticFormulationRepository;
    }

    /**
     * @param Event                 $event
     * @param DuplicatorDataStorage $duplicatorDataStorage
     */
    public function duplicate(Event $event, DuplicatorDataStorage $duplicatorDataStorage): void
    {
        $staticFormulations = $this->staticFormulationRepository->findByEvent($event->getDuplicatedFrom());

        foreach ($staticFormulations as $staticFormulation) {
            $types = [];

            foreach ($staticFormulation->getTypes() as $type) {
                $types[] = $duplicatorDataStorage->types[$type->getId()];
            }

            $newStaticFormulation = new StaticFormulation(
                $event,
                $staticFormulation->getKey(),
                $types
            );

            foreach ($event->getLocales() as $locale) {
                $newStaticFormulation->translate(
                    $locale,
                    $staticFormulation->getTitle($locale)
                );
            }

            $this->staticFormulationRepository->add($newStaticFormulation);
        }
    }
}
