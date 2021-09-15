<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Group;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\SheetGroupCreatedEvent;
use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CreateHandler
{
    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * @param GroupRepositoryInterface $groupRepository
     * @param SheetRepositoryInterface $sheetRepository
     * @param \DateTimeInterface       $dateTime
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        SheetRepositoryInterface $sheetRepository,
        \DateTimeInterface $dateTime,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->groupRepository = $groupRepository;
        $this->sheetRepository = $sheetRepository;
        $this->dateTime        = $dateTime;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(Create $command): void
    {
        $group = new Group(
            $command->event,
            $command->user,
            $command->title,
            $command->forceSheetTitle,
            $this->dateTime
        );

        foreach ($command->sheetViews as $sheetView) {
            $sheet = $this->sheetRepository->getSheetById($sheetView->id);

            if (!$sheet->hasGroup()) {
                $sheet->setGroup($group);
            }
        }

        $this->groupRepository->add($group);

        $this->eventDispatcher->dispatch(Events::SHEET_GROUP_CREATED,
            new SheetGroupCreatedEvent($group)
        );
    }
}
