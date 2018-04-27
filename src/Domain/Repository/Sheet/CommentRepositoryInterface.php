<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Comment;

interface CommentRepositoryInterface
{
    /**
     * @param Comment $comment
     */
    public function add(Comment $comment);

    /**
     * @param Sheet $sheet
     *
     * @return Comment[]
     */
    public function getCommentsBySheet(Sheet $sheet);
}
