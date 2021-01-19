<?php

namespace Proximum\Vimeet\Tests\Ui\Bundle\AdminBundle\Controller\OMZ;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\FileSystemAdapterInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\HttpFoundation\Response\CsvFileResponse;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\OMZ\DownloadAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DownloadActionTest extends TestCase
{
    /** @var ObjectProphecy */
    private $authorizationCheckerAdapter;

    /** @var ObjectProphecy */
    private $fileSystemAdapter;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $file;

    /** @var string */
    private $hash;

    /** @var string */
    private $path;

    public function setUp()
    {
        $this->authorizationCheckerAdapter = $this->prophesize(AuthorizationCheckerAdapterInterface::class);
        $this->fileSystemAdapter = $this->prophesize(FileSystemAdapterInterface::class);
        $this->file = $this->prophesize(File::class);
        $this->event = $this->prophesize(Event::class);
        $this->hash = 'hash';
        $this->path = '/tmp/path/to/omz/export';
    }

    public function testAuthorizationEventAccess()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new DownloadAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->fileSystemAdapter->reveal(),
            $this->path
        );
        $action($this->event->reveal(), $this->hash, $this->file->reveal());
    }

    public function testAuthorizationOrganizer()
    {
        $this->expectException(AccessDeniedException::class);

        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(false)
        ;

        $action = new DownloadAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->fileSystemAdapter->reveal(),
            $this->path
        );
        $action($this->event->reveal(), $this->hash, $this->file->reveal());
    }


    public function testFileHash()
    {
        $this->expectException(NotFoundHttpException::class);

        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->file->getHash()->willReturn('other-hash');
        $this->file->getId()->willReturn(12);

        $action = new DownloadAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->fileSystemAdapter->reveal(),
            $this->path
        );
        $action($this->event->reveal(), $this->hash, $this->file->reveal());
    }

    public function testFileNotExist()
    {
        $this->expectException(NotFoundHttpException::class);

        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->file->getHash()->willReturn('hash');
        $this->file->getPath()->willReturn('/path.csv');
        $this->file->getId()->willReturn(12);

        $this->fileSystemAdapter->exists($this->path . '/path.csv')->shouldBeCalled()->willReturn(false);

        $action = new DownloadAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->fileSystemAdapter->reveal(),
            $this->path
        );
        $action($this->event->reveal(), $this->hash, $this->file->reveal());
    }

    public function testResponse()
    {
        $this->authorizationCheckerAdapter->isGranted('PERMISSION_EVENT_ACCESS', $this->event->reveal())
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->authorizationCheckerAdapter->isGranted('ROLE_ALLOWED_TO_ORGANIZE')
            ->shouldBeCalled()
            ->willReturn(true)
        ;

        $this->file->getHash()->willReturn('hash');
        $this->file->getPath()->willReturn('/DownloadActionTest.php');

        $this->fileSystemAdapter->exists(__DIR__ . '/DownloadActionTest.php')->shouldBeCalled()->willReturn(true);

        $action = new DownloadAction(
            $this->authorizationCheckerAdapter->reveal(),
            $this->fileSystemAdapter->reveal(),
            __DIR__
        );
        $response = $action($this->event->reveal(), $this->hash, $this->file->reveal());

        $this->assertInstanceOf(CsvFileResponse::class, $response);
    }
}
