<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\AddComment;
use Proximum\Vimeet\Application\Command\Sheet\AddCommentHandler;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Sheet\Comment;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Sheet\CommentRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class AddCommentHandlerTest extends TestCase
{
    public function testHandle()
    {
        $dateTime = new \DateTime();
        $event    = EventFactory::createEvent();
        $type     = new Type($event);
        $user     = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet    = new Sheet($event, $type, [], $user, $dateTime);
        $author   = new Admin('test@test.com', '__SALT__', '__PASSWORD__', 'fr', 'Truc', 'Muche', 'ROLE_SUPER_ADMIN', $dateTime);

        $addComment = new AddComment(
            $sheet,
            $author,
            $dateTime
        );
        $addComment->text = 'text';

        // expected
        $expectedComment = new Comment(
            $sheet,
            $author,
            'text',
            $dateTime
        );

        // mock
        $commentRepository = $this->prophesize(CommentRepositoryInterface::class);
        $commentRepository->add($expectedComment)->shouldBeCalled();

        $command = new AddCommentHandler($commentRepository->reveal());
        $command->handle($addComment);
    }
}
