<?php

namespace Proximum\Vimeet\Tests\Application\Command\Type\Badge;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Type\Badge\Configure;
use Proximum\Vimeet\Application\Command\Type\Badge\ConfigureHandler;
use Proximum\Vimeet\Application\Command\Type\Badge\MirroringAndFullHeightImageIncompatibilityException;
use Proximum\Vimeet\Application\Command\Type\Badge\NoLeftImageToRemoveException;
use Proximum\Vimeet\Application\Command\Type\Badge\NoRightImageToRemoveException;
use Proximum\Vimeet\Application\Command\Type\Badge\NoRightImageToSetFullHeightException;
use Proximum\Vimeet\Application\Command\Type\Badge\RemovingWhileAddingLeftImageException;
use Proximum\Vimeet\Application\Command\Type\Badge\RemovingWhileAddingRightImageException;
use Proximum\Vimeet\Domain\Model\Badge;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\BadgeRepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ConfigureHandlerTest extends TestCase
{
    public function testMirroringAndFullHeightImageIncompatibilityException(): void
    {
        $this->expectException(MirroringAndFullHeightImageIncompatibilityException::class);

        // prepare data
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);

        // prophecy dependencies
        $badgeRepository = $this->prophesize(BadgeRepositoryInterface::class);
        $fileStorage = $this->prophesize(FileStorageInterface::class);

        // run tests
        $handler = new ConfigureHandler($badgeRepository->reveal(), $fileStorage->reveal());
        $command = new Configure($event->reveal(), $type->reveal());
        $command->isRightImageFullHeight = true;
        $command->isMirrored = true;

        $handler->handle($command);
    }

    public function testRemovingWhileAddingLeftImageException(): void
    {
        $this->expectException(RemovingWhileAddingLeftImageException::class);

        // prepare data
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);

        $leftImage = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'jpeg'])
            ->getMock()
        ;

        // prophecy dependencies
        $badgeRepository = $this->prophesize(BadgeRepositoryInterface::class);
        $fileStorage = $this->prophesize(FileStorageInterface::class);

        // run tests
        $handler = new ConfigureHandler($badgeRepository->reveal(), $fileStorage->reveal());
        $command = new Configure($event->reveal(), $type->reveal());
        $command->removeLeftImage = true;
        $command->leftImage = $leftImage;

        $handler->handle($command);
    }

    public function testRemovingWhileAddingRightImageException(): void
    {
        $this->expectException(RemovingWhileAddingRightImageException::class);

        // prepare data
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);

        $rightImage = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'jpeg'])
            ->getMock()
        ;

        // prophecy dependencies
        $badgeRepository = $this->prophesize(BadgeRepositoryInterface::class);
        $fileStorage = $this->prophesize(FileStorageInterface::class);

        // run tests
        $handler = new ConfigureHandler($badgeRepository->reveal(), $fileStorage->reveal());
        $command = new Configure($event->reveal(), $type->reveal());
        $command->removeRightImage = true;
        $command->rightImage = $rightImage;

        $handler->handle($command);
    }

    public function testNoLeftImageToRemoveException(): void
    {
        $this->expectException(NoLeftImageToRemoveException::class);

        // prepare data
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);

        // prophecy dependencies
        $badgeRepository = $this->prophesize(BadgeRepositoryInterface::class);
        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $badgeRepository->set(Argument::any())->shouldNotBeCalled();
        $badgeRepository->add(Argument::any())->shouldNotBeCalled();

        // run tests
        $handler = new ConfigureHandler($badgeRepository->reveal(), $fileStorage->reveal());
        $command = new Configure($event->reveal(), $type->reveal());
        $command->removeLeftImage = true;

        $handler->handle($command);
    }

    public function testNoRightImageToRemoveException(): void
    {
        $this->expectException(NoRightImageToRemoveException::class);

        // prepare data
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);

        // prophecy dependencies
        $badgeRepository = $this->prophesize(BadgeRepositoryInterface::class);
        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $badgeRepository->set(Argument::any())->shouldNotBeCalled();
        $badgeRepository->add(Argument::any())->shouldNotBeCalled();

        // run tests
        $handler = new ConfigureHandler($badgeRepository->reveal(), $fileStorage->reveal());
        $command = new Configure($event->reveal(), $type->reveal());
        $command->removeRightImage = true;

        $handler->handle($command);
    }

    public function testNoRightImageToSetFullHeightException(): void
    {
        $this->expectException(NoRightImageToSetFullHeightException::class);

        // prepare data
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);

        // prophecy dependencies
        $badgeRepository = $this->prophesize(BadgeRepositoryInterface::class);
        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $badgeRepository->set(Argument::any())->shouldNotBeCalled();
        $badgeRepository->add(Argument::any())->shouldNotBeCalled();

        // run tests
        $handler = new ConfigureHandler($badgeRepository->reveal(), $fileStorage->reveal());
        $command = new Configure($event->reveal(), $type->reveal());
        $command->isRightImageFullHeight = true;

        $handler->handle($command);
    }

    public function testNew(): void
    {
        // prepare data
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);

        $rightImage = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'jpeg'])
            ->getMock()
        ;

        $expectedBadge = new Badge($event->reveal(), $type->reveal());
        $expectedBadge->update(
            null,
            true,
            Badge::FOOTER_SHOW_TYPE,
            '#ffffff',
            '#000000',
            false,
            true,
            true,
            true,
            true,
            true,
            false,
            false,
            false,
            [],
            false,
            null,
            'rightImage.jpg'
        );

        // prophecy dependencies
        $badgeRepository = $this->prophesize(BadgeRepositoryInterface::class);
        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $badgeRepository->set(Argument::any())->shouldNotBeCalled();
        $badgeRepository->add($expectedBadge)->shouldBeCalled();

        $fileStorage->upload($rightImage)->shouldBeCalled()->willReturn('rightImage.jpg');

        // run tests
        $handler = new ConfigureHandler($badgeRepository->reveal(), $fileStorage->reveal());
        $command = new Configure($event->reveal(), $type->reveal());
        $command->showPosition = false;
        $command->rightImage = $rightImage;

        $handler->handle($command);
    }

    public function testEdit(): void
    {
        // prepare data
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);
        $badge = new Badge($event->reveal(), $type->reveal());

        $rightImage = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'jpeg'])
            ->getMock()
        ;

        $expectedBadge = new Badge($event->reveal(), $type->reveal());
        $expectedBadge->update(
            null,
            true,
            Badge::FOOTER_SHOW_TYPE,
            '#ffffff',
            '#000000',
            false,
            true,
            true,
            true,
            true,
            true,
            false,
            false,
            false,
            [],
            false,
            null,
            'rightImage.jpg'
        );

        // prophecy dependencies
        $badgeRepository = $this->prophesize(BadgeRepositoryInterface::class);
        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $badgeRepository->add(Argument::any())->shouldNotBeCalled();
        $badgeRepository->set($expectedBadge)->shouldBeCalled();

        $fileStorage->upload($rightImage)->shouldBeCalled()->willReturn('rightImage.jpg');

        // run tests
        $handler = new ConfigureHandler($badgeRepository->reveal(), $fileStorage->reveal());
        $command = new Configure($event->reveal(), $type->reveal(), $badge);
        $command->showPosition = false;
        $command->rightImage = $rightImage;

        $handler->handle($command);
    }

    public function testDeleteLeftImage(): void
    {
        // prepare data
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);
        $badge = new Badge($event->reveal(), $type->reveal());

        $badge->update(
            null,
            true,
            Badge::FOOTER_SHOW_TYPE,
            '#ffffff',
            '#000000',
            true,
            true,
            true,
            true,
            true,
            true,
            false,
            false,
            false,
            [],
            false,
            'leftImage.jpg'
        );

        $expectedBadge = new Badge($event->reveal(), $type->reveal());
        $expectedBadge->update(
            null,
            true,
            Badge::FOOTER_SHOW_TYPE
        );

        // prophecy dependencies
        $badgeRepository = $this->prophesize(BadgeRepositoryInterface::class);
        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $badgeRepository->add(Argument::any())->shouldNotBeCalled();
        $badgeRepository->set($expectedBadge)->shouldBeCalled();
        $fileStorage->remove('leftImage.jpg')->shouldBeCalled();

        // run tests
        $handler = new ConfigureHandler($badgeRepository->reveal(), $fileStorage->reveal());
        $command = new Configure($event->reveal(), $type->reveal(), $badge);
        $command->removeLeftImage = true;

        $handler->handle($command);
    }

    public function testDeleteRightImage(): void
    {
        // prepare data
        $event = $this->prophesize(Event::class);
        $type = $this->prophesize(Type::class);
        $badge = new Badge($event->reveal(), $type->reveal());

        $badge->update(
            null,
            true,
            Badge::FOOTER_SHOW_TYPE,
            '#ffffff',
            '#000000',
            true,
            true,
            true,
            true,
            true,
            true,
            false,
            false,
            false,
            [],
            false,
            'leftImage.jpg',
            'rightImage.jpg'
        );

        $expectedBadge = new Badge($event->reveal(), $type->reveal());
        $expectedBadge->update(
            null,
            true,
            Badge::FOOTER_SHOW_TYPE,
            '#ffffff',
            '#000000',
            true,
            true,
            true,
            true,
            true,
            true,
            false,
            false,
            false,
            [],
            false,
            'leftImage.jpg'
        );

        // prophecy dependencies
        $badgeRepository = $this->prophesize(BadgeRepositoryInterface::class);
        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $badgeRepository->add(Argument::any())->shouldNotBeCalled();
        $badgeRepository->set($expectedBadge)->shouldBeCalled();
        $fileStorage->remove('rightImage.jpg')->shouldBeCalled();

        // run tests
        $handler = new ConfigureHandler($badgeRepository->reveal(), $fileStorage->reveal());
        $command = new Configure($event->reveal(), $type->reveal(), $badge);
        $command->removeRightImage = true;

        $handler->handle($command);
    }
}
