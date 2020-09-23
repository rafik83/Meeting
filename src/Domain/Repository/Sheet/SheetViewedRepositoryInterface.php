<?php

namespace Proximum\Vimeet\Domain\Repository\Sheet;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\SheetViewed;
use Proximum\Vimeet\Domain\Model\User;

interface SheetViewedRepositoryInterface
{
    /** @param SheetViewed $sheetViewed */
    public function add(SheetViewed $sheetViewed): void;

    public function isSheetAlreadySeenByUser(User $user, Sheet $sheet): bool;

    /**
     * @param User  $user
     * @param array $sheetIds
     *
     * @return SheetViewed[]
     */
    public function getSheetsAlreadySeenByUser(User $user, array $sheetIds): array;

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return int[]
     */
    public function getSheetsSeenByUserAndEvent(User $user, Event $event): array;

    /**
     * @param Sheet $sheet
     *
     * @return int[]
     */
    public function getUsersWhoViewedSheet(Sheet $sheet): array;
}
