<?php

namespace Proximum\Vimeet\Application\Command\StaticFormulation;

use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Repository\StaticFormulation\StaticFormulationRepositoryInterface;

class CreateHandler
{
    /** @var StaticFormulationRepositoryInterface */
    private $staticFormulationRepository;

    public function __construct(StaticFormulationRepositoryInterface $staticFormulationRepository)
    {
        $this->staticFormulationRepository = $staticFormulationRepository;
    }

    public function handle(Create $command): void
    {
        $staticFormulation = new StaticFormulation(
            $command->event,
            $command->key,
            $command->types
        );

        foreach ($command->translations as $locale => $translation) {
            $staticFormulation->translate($locale, $translation['title']);
        }

        $this->staticFormulationRepository->add($staticFormulation);
    }
}
