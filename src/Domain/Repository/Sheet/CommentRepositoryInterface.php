<?php

namespace Proximum\Vimeet\Domain\Repository\Sheet;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Comment;

interface CommentRepositoryInterface
{
    /**
     * @param Comment $comment
     */
    public function add(Comment $comment);

    public function remove(Comment $comment): void;

    /**
     * @param Sheet $sheet
     *
     * @return Comment[]
     */
    public function getCommentsBySheet(Sheet $sheet);
}
