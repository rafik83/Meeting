<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
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
        if ($addComment->text !== null) {
            $this->commentRepository->add(
                new Comment(
                    $addComment->sheet,
                    $addComment->author,
                    $addComment->text,
                    $this->dateTime
                )
            );
        }

        if ($addComment->commercialStatus !== $addComment->sheet->getCommercialStatus()) {
            $addComment->sheet->setCommercialStatus($addComment->commercialStatus);

            $this->sheetRepository->set($addComment->sheet);
        }
    }
}
