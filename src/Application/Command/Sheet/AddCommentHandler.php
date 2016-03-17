<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet\Comment;
use Proximum\Vimeet\Domain\Repository\Sheet\CommentRepositoryInterface;

class AddCommentHandler
{
    /**
     * @var CommentRepositoryInterface
     */
    private $commentRepository;

    /**
     * @param CommentRepositoryInterface $commentRepository
     */
    public function __construct(CommentRepositoryInterface $commentRepository)
    {
        $this->commentRepository = $commentRepository;
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
                $addComment->date
            )
        );
    }
}
