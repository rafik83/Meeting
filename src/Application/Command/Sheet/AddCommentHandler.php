<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet\Comment;
use Proximum\Vimeet\Domain\Repository\Sheet\CommentRepositoryInterface;

class AddCommentHandler
{
    /** @var CommentRepositoryInterface */
    private $commentRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /**
     * @param CommentRepositoryInterface $commentRepository
     * @param \DateTimeInterface         $dateTime
     */
    public function __construct(CommentRepositoryInterface $commentRepository, \DateTimeInterface $dateTime)
    {
        $this->commentRepository = $commentRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * @param AddComment $addComment
     */
    public function handle(AddComment $addComment)
    {
        $this->commentRepository->add(
            new Comment(
                $addComment->sheet,
                $addComment->author,
                $addComment->text,
                $this->dateTime
            )
        );
    }
}
