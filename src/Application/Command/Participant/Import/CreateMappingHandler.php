<?php

namespace Proximum\Vimeet\Application\Command\Participant\Import;

use Proximum\Vimeet\Domain\Exception\Sheet\ImportMapping\TitleNotUniqueException;
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
            Throw new TitleNotUniqueException("Title already used : $createMapping->title");
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
