<?php

namespace Proximum\Vimeet\Application\Command\Participant\Import;

use Proximum\Vimeet\Domain\Repository\Sheet\ImportMappingRepositoryInterface;

class UpdateMappingHandler
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

    public function handle(UpdateMapping $updateMapping): void
    {
        $updateMapping->importMapping->update(
            $updateMapping->mapping,
            $this->dateTime
        );

        $this->importMappingRepository->update($updateMapping->importMapping);
    }
}
