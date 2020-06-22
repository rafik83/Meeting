<?php

namespace Proximum\Vimeet\Application\Command\Participant\Import;

use Proximum\Vimeet\Domain\Model\Sheet\ImportMapping;
use Proximum\Vimeet\Domain\Repository\Sheet\ImportMappingRepositoryInterface;

class CreateMappingHandler
{
    /** @var ImportMappingRepositoryInterface */
    private $importMappingRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        ImportMappingRepositoryInterface $importMappingRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->importMappingRepository = $importMappingRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(CreateMapping $createMapping): void
    {
        if ($this->importMappingRepository->hasExistingMappingWithTitle(
            $createMapping->event,
            $createMapping->title
        )) {
            /** @todo change exception to a domain one */
            Throw new \InvalidArgumentException('Same title for mapping');
        }

        $mapping = new ImportMapping(
            $createMapping->event,
            $createMapping->title,
            $createMapping->mapping,
            $this->dateTime
        );

        $this->importMappingRepository->add($mapping);
    }
}
