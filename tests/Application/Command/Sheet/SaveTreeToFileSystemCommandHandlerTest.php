<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Application\Adapter\UserEventDecryptFileInterface;
use Proximum\Vimeet\Application\Command\Sheet\SaveTreeToFileSystemCommand;
use Proximum\Vimeet\Application\Command\Sheet\SaveTreeToFileSystemCommandHandler;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectNodeView;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectsTreeView;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SaveTreeToFileSystemCommandHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $sharedUploadedFiles = '/path/to/uploaded/files';
        $encryptedFilesPath = '/path/to/encrypted/files';
        $webDir = '/path/to/web';

        $userEventDecryptFile = $this->prophesize(UserEventDecryptFileInterface::class);
        $fileSystemAdapter = $this->prophesize(FileSystemAdapterInterface::class);
        $user = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $event = $this->prophesize(Event::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->getOwner()->shouldBeCalled()->willReturn($user2->reveal());
        $sheet1->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $sheet2 = $this->prophesize(Sheet::class);

        $node1 = new UploadedObjectNodeView('Mb7d3M765e', 'Label 1');
        $node1->addUploadedObjectView(new UploadedObjectView('/path/to/file1', '1-title-1.jpg', true, $sheet1->reveal()));
        $node2 = new UploadedObjectNodeView('Med79Mea70', 'Label 2');
        $node2->addUploadedObjectView(new UploadedObjectView('/path/to/file2', '2-title-2-1-mathieu-marchois.jpg', false, $sheet2->reveal(), $user->reveal()));
        $node2->addUploadedObjectView(new UploadedObjectView('/path/to/file3', '2-title-2-2-richard-hanna.jpg', false, $sheet2->reveal(), $user2->reveal()));

        $tree = new UploadedObjectsTreeView();
        $tree->addNode($node1, 'Mb7d3M765e');
        $tree->addNode($node2, 'Med79Mea70');

        $fileSystemAdapter->exists('/path/to/encrypted/files/path/to/file1')->shouldBeCalled()->willReturn(true);

        $userEventDecryptFile
            ->decryptFile($event->reveal(), $user2->reveal(), '/path/to/encrypted/files/path/to/file1', Argument::any())
            ->shouldBeCalled();

        $fileSystemAdapter->mkdir(Argument::any())
            ->shouldBeCalled();

        $fileSystemAdapter->exists('/path/to/web/path/to/file2')->shouldBeCalled()->willReturn(true);
        $fileSystemAdapter->copy('/path/to/web/path/to/file2', Argument::any())
            ->shouldBeCalled();

        $fileSystemAdapter->exists('/path/to/web/path/to/file3')->shouldBeCalled()->willReturn(true);
        $fileSystemAdapter->copy('/path/to/web/path/to/file3', Argument::any())
            ->shouldBeCalled();

        $handler = new SaveTreeToFileSystemCommandHandler(
            $userEventDecryptFile->reveal(),
            $fileSystemAdapter->reveal(),
            $sharedUploadedFiles,
            $encryptedFilesPath,
            $webDir
        );
        $handler->handle(new SaveTreeToFileSystemCommand($tree));
    }
}
