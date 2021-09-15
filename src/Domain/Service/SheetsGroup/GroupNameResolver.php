<?php

namespace Proximum\Vimeet\Domain\Service\SheetsGroup;

use Proximum\Vimeet\Application\Exception\Sheet\SheetNotFoundException;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\User\Sheet\FirstParticipantSheetOfUserGetter;

class GroupNameResolver
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var FirstParticipantSheetOfUserGetter */
    private $firstParticipantSheetOfUserGetter;

    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        FirstParticipantSheetOfUserGetter $firstParticipantSheetOfUserGetter
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->firstParticipantSheetOfUserGetter = $firstParticipantSheetOfUserGetter;
    }

    public function resolve(Event $event, User $user, array $sheets = []): ?string
    {
        if (empty($sheets)) {
            $sheets = $this->sheetRepository->getSheetsByUserAndEvent($user, $event);

            if (empty($sheets)) {
                throw new SheetNotFoundException('Sheet not found.');
            }
        }

        foreach ($sheets as $sheet) {
            $group = $sheet->getGroup();

            if ($group instanceof Sheet\Group && !$group->hasSheetTitleForced()) {
                return $group->getTitle();
            }
        }

        $sheet = $this->firstParticipantSheetOfUserGetter->getFirstParticipantSheet($user, $sheets);

        if ($sheet instanceof Sheet) {
            return $sheet->getTitle();
        }

        $sheet = reset($sheets);

        if (!$sheet instanceof Sheet) {
            throw new SheetNotFoundException('Sheet not found.');
        }

        return $sheet->getTitle();
    }
}
