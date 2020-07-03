<?php

namespace Proximum\Vimeet\Application\Query\User;

use Proximum\Vimeet\Application\View\User\UserDetailsView;
use Proximum\Vimeet\Application\View\User\UserSheetView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\UserEvent\Exception\UserEventMissingException;

class UserDetailsViewQueryHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    public function __construct(SheetRepositoryInterface $sheetRepository)
    {
        $this->sheetRepository     = $sheetRepository;
    }

    /**
     * @throws UserEventMissingException
     */
    public function handle(UserDetailsViewQuery $query): UserDetailsView
    {
        $userSheetListView = [];

        $sheets = $this->sheetRepository->getByUser($query->user);

        if (!$this->hasSheetInEvent($sheets, $query->event)) {
            throw new UserEventMissingException(
                sprintf(
                    'This user %s is not on this event %s',
                    $query->user->getId(),
                    $query->event->getId()
                )
            );
        }

        foreach ($sheets as $sheet) {
            $userSheetListView[] = new UserSheetView($sheet);
        }

        return new UserDetailsView($query->event, $query->user, $userSheetListView);
    }

    private function hasSheetInEvent(array $sheets, Event $event): bool
    {
        foreach ($sheets as $sheet) {
            if ($sheet->getEvent() === $event) {
                return true;
            }
        }

        return false;
    }
}
