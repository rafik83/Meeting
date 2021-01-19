<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Application\Command\Encryption\Decrypt;
use Proximum\Vimeet\Application\Command\Encryption\DecryptHandler;
use Proximum\Vimeet\Application\Command\Sheet\SaveTreeToFileSystemCommand;
use Proximum\Vimeet\Application\Command\Sheet\SaveTreeToFileSystemCommandHandler;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectNodeView;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectsTreeView;
use Proximum\Vimeet\Application\View\Sheet\UploadedObjectView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class SaveTreeToFileSystemCommandHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $sharedUploadedFiles = '/path/to/uploaded/files';
        $encryptedFilesPath = '/path/to/encrypted/files';
        $webDir = '/path/to/web';

        $fileSystemAdapter = $this->prophesize(FileSystemAdapterInterface::class);
        $user = $this->prophesize(User::class);
        $user2 = $this->prophesize(User::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);

        $node1 = new UploadedObjectNodeView('Mb7d3M765e');
        $node1->addUploadedObjectView(
            new UploadedObjectView('/path/to/file1', '1-title-1.jpg', true, $sheet1->reveal(), null, true)
        );
        $node1->addUploadedObjectView(
            new UploadedObjectView(
                '/path/to/file4',
                '4-title-4-1-jean-pauk-sartre.jpg',
                true,
                $sheet1->reveal(),
                $user->reveal(),
                false
            )
        );
        $node2 = new UploadedObjectNodeView('Med79Mea70');
        $node2->addUploadedObjectView(
            new UploadedObjectView(
                '/path/to/file2',
                '2-title-2-1-jean-pauk-sartre.jpg',
                false,
                $sheet2->reveal(),
                $user->reveal(),
                false
            )
        );
        $node2->addUploadedObjectView(
            new UploadedObjectView(
                '/path/to/file3',
                '2-title-2-2-simone-de-beauvoir.jpg',
                false,
                $sheet2->reveal(),
                $user2->reveal(),
                false
            )
        );

        $tree = new UploadedObjectsTreeView();
        $tree->addNode($node1, 'Mb7d3M765e');
        $tree->addNode($node2, 'Med79Mea70');

        $fileSystemAdapter->exists('/path/to/encrypted/files/path/to/file1')->shouldBeCalled()->willReturn(true);
        $fileSystemAdapter->exists('/path/to/encrypted/files/path/to/file4')->shouldBeCalled()->willReturn(true);

        $decryptHandler = $this->prophesize(DecryptHandler::class);
        $decryptHandler
            ->handle(
                Argument::that(function (Decrypt $decrypt) use ($sheet1) {
                    return $decrypt->sheet === $sheet1->reveal()
                        && $decrypt->isSheetData
                        && null === $decrypt->user
                        && '/path/to/encrypted/files/path/to/file1' === $decrypt->encryptedFilePath;
                })
            )
            ->shouldBeCalled()
        ;

        $decryptHandler
            ->handle(
                Argument::that(function (Decrypt $decrypt) use ($sheet1, $user) {
                    return $decrypt->sheet === $sheet1->reveal()
                        && !$decrypt->isSheetData
                        && $user->reveal() === $decrypt->user
                        && '/path/to/encrypted/files/path/to/file4' === $decrypt->encryptedFilePath;
                })
            )
            ->shouldBeCalled()
        ;

        $fileSystemAdapter->mkdir(Argument::any())
            ->shouldBeCalled();

        $fileSystemAdapter->exists('/path/to/web/path/to/file2')->shouldBeCalled()->willReturn(true);
        $fileSystemAdapter->copy('/path/to/web/path/to/file2', Argument::any())
            ->shouldBeCalled();

        $fileSystemAdapter->exists('/path/to/web/path/to/file3')->shouldBeCalled()->willReturn(true);
        $fileSystemAdapter->copy('/path/to/web/path/to/file3', Argument::any())
            ->shouldBeCalled();

        $handler = new SaveTreeToFileSystemCommandHandler(
            $decryptHandler->reveal(),
            $fileSystemAdapter->reveal(),
            $sharedUploadedFiles,
            $encryptedFilesPath,
            $webDir
        );
        $handler->handle(new SaveTreeToFileSystemCommand($tree));
    }
}
