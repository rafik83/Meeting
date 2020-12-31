<?php


namespace Proximum\Vimeet\Application\Command\StaticFormulation;

use Proximum\Vimeet\Domain\Repository\StaticFormulation\StaticFormulationRepositoryInterface;

class UpdateHandler
{
    /** @var StaticFormulationRepositoryInterface */
    private $staticFormulationRepository;

    public function __construct(StaticFormulationRepositoryInterface $staticFormulationRepository)
    {
        $this->staticFormulationRepository = $staticFormulationRepository;
    }

    public function handle(Update $command): void
    {
        $staticFormulation = $command->staticFormulation;

        foreach ($command->translations as $locale => $translation) {
            $staticFormulation->translate($locale, $translation['title']);
        }

        $staticFormulation->update($command->types);

        $this->staticFormulationRepository->set($staticFormulation);
    }
}
