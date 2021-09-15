<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Sheet\CommercialStatusChanged;
use Proximum\Vimeet\Domain\Model\Sheet\Comment;
use Proximum\Vimeet\Domain\Repository\Sheet\CommentRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class AddCommentHandler
{
    /** @var CommentRepositoryInterface */
    private $commentRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var DelayedEventDispatcherInterface */
    private $eventDispatcher;

    /**
     * @param SheetRepositoryInterface        $sheetRepository
     * @param CommentRepositoryInterface      $commentRepository
     * @param DelayedEventDispatcherInterface $eventDispatcher
     * @param \DateTimeInterface              $dateTime
     */
    public function __construct(
        SheetRepositoryInterface $sheetRepository,
        CommentRepositoryInterface $commentRepository,
        DelayedEventDispatcherInterface $eventDispatcher,
        \DateTimeInterface $dateTime
    ) {
        $this->sheetRepository = $sheetRepository;
        $this->commentRepository = $commentRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->dateTime = $dateTime;
    }

    /**
     * @param AddComment $addComment
     */
    public function handle(AddComment $addComment)
    {
        if (null !== $addComment->text) {
            $this->commentRepository->add(
                new Comment(
                    $addComment->sheet,
                    $addComment->author,
                    $addComment->text,
                    $this->dateTime
                )
            );
        }

        $isReminderDateUpdated     = $addComment->reminderDate !== $addComment->sheet->getReminderDate();
        $isCommercialStatusUpdated = $addComment->commercialStatus !== $addComment->sheet->getCommercialStatus();

        if ($isReminderDateUpdated || $isCommercialStatusUpdated) {
            if ($isReminderDateUpdated) {
                $addComment->sheet->setReminderDate($addComment->reminderDate);
            }

            if ($isCommercialStatusUpdated) {
                $addComment->sheet->setCommercialStatus($addComment->commercialStatus);
            }

            $this->sheetRepository->set($addComment->sheet);

            if ($isCommercialStatusUpdated) {
                $this->eventDispatcher->dispatch(
                    Events::SHEET_SET_COMMERCIAL_STATUS,
                    new CommercialStatusChanged($addComment->sheet, $addComment->author, $this->dateTime)
                );
            }
        }
    }
}
