<?php

namespace Proximum\Vimeet\Domain\User\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class HasAccessToSheet
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository = $sheetRepository;
    }

    public function isSatisfiedBy(User $user, Event $event, Sheet $sheet): bool
    {
        $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);

        foreach ($sheets as $userSheet) {
            if ($userSheet->getId() === $sheet->getId()) {
                return true;
            }
        }

        return false;
    }
}
