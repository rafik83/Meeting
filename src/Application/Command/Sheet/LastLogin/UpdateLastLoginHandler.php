<?php

namespace Proximum\Vimeet\Application\Command\Sheet\LastLogin;

use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class UpdateLastLoginHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(SheetRepositoryInterface $sheetRepository, \DateTimeInterface $dateTime)
    {
        $this->sheetRepository = $sheetRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(UpdateLastLogin $updateLastLogin): void
    {
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($updateLastLogin->user, $updateLastLogin->event);

        foreach ($sheets as $sheet) {
            $this->sheetRepository->set($sheet->setLastLoginAt($this->dateTime));
        }
    }
}
