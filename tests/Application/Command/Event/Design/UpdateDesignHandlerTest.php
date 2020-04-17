<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Event\Design;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Event\Configuration\Background\RemoveImage;
use Proximum\Vimeet\Application\Command\Event\Configuration\Background\RemoveImageHandler;
use Proximum\Vimeet\Application\Command\Event\Design\UpdateDesign;
use Proximum\Vimeet\Application\Command\Event\Design\UpdateDesignHandler;
use Proximum\Vimeet\Application\Components\Guideline\Generator;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UpdateDesignHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $fileStorage;

    /** @var ObjectProphecy */
    private $removeImageHandler;

    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $configuration;

    /** @var ObjectProphecy */
    private $guidelineGenerator;

    /** @var ObjectProphecy */
    private $eventRepository;

    public function setUp()
    {
        $this->removeImageHandler = $this->prophesize(RemoveImageHandler::class);
        $this->fileStorage = $this->prophesize(FileStorageInterface::class);
        $this->guidelineGenerator = $this->prophesize(Generator::class);
        $this->eventRepository = $this->prophesize(EventRepositoryInterface::class);

        $this->event = $this->prophesize(Event::class);
        $this->configuration = $this->prophesize(Event\Configuration::class);
        $this->event->getConfiguration()->willReturn($this->configuration->reveal());
    }

    public function testHandle(): void
    {
        $this->event->getLocales()->willReturn(['fr', 'en']);
        $this->configuration->getHeaderLeftColor()->willReturn('#AAAAAA');
        $this->configuration->getHeaderRightColor()->willReturn('#BBBBBB');
        $this->configuration->getLeftColor()->willReturn('#CCCCCC');
        $this->configuration->getRightColor()->willReturn('#DDDDDD');
        $this->configuration->getBackgroundColor()->willReturn('#EEEEEE');
        $this->configuration->getTextColor()->willReturn('#FFFFFF');
        $this->configuration->getHeaderButtonLeftColor()->willReturn('#2F2F2F');
        $this->configuration->getHeaderButtonRightColor()->willReturn('#2F2F2F');
        $this->configuration->getHeaderButtonTextColor()->willReturn('#FFFFFF');
        $this->configuration->getBackgroundImage()->willReturn(null);

        $this->event->getLocalizedLogo('fr')->willReturn('logoFr.png');
        $this->event->getLocalizedLogoExtension('fr')->willReturn('png');
        $this->event->getLocalizedMobileLogo('fr')->willReturn(null);
        $this->event->getLocalizedMobileLogoExtension('fr')->willReturn(null);
        $this->event->getLocalizedNotificationImage('fr')->willReturn('notificationImageFr.png');
        $this->event->getLocalizedNotificationImageExtension('fr')->willReturn('png');

        $this->event->getLocalizedLogo('en')->willReturn('logoEn.jpeg');
        $this->event->getLocalizedLogoExtension('en')->willReturn('jpeg');
        $this->event->getLocalizedMobileLogo('en')->willReturn('mobileLogoEn.jpeg');
        $this->event->getLocalizedMobileLogoExtension('en')->willReturn('jpeg');
        $this->event->getLocalizedNotificationImage('en')->willReturn(null);
        $this->event->getLocalizedNotificationImageExtension('en')->willReturn(null);

        $this->guidelineGenerator->generate($this->event->reveal())->shouldBeCalled()->willReturn('/path/asset/main.css');
        $this->fileStorage->remove('logoFr.png')->shouldBeCalled();
        $this->fileStorage->remove('mobileLogoEn.jpeg')->shouldBeCalled();
        $this->fileStorage->remove(null)->shouldBeCalled();
        $this->fileStorage->remove('notificationImageFr.png')->shouldBeCalled();
        $this->fileStorage->upload(Argument::that(function (UploadedFile $uploaded) {
            return true;
        }))->shouldBeCalled()->willReturn('newLogoFr.jpeg',  'newNotificationImageFr.png', 'newMobileLogoEn.png', 'background.png');
        $this->fileStorage->getExtension(Argument::that(function (UploadedFile $uploaded) {
            return true;
        }))->shouldBeCalled()->willReturn('jpeg', 'png');

        $this->event->updateLocalizedLogos(
            'fr',
            'newLogoFr.jpeg',
            'jpeg',
            null,
            null,
            'newNotificationImageFr.png',
            'png'
        )->shouldBeCalled();
        $this->event->updateLocalizedLogos(
            'en',
            'logoEn.jpeg',
            'jpeg',
            'newMobileLogoEn.png',
            'png',
            null,
            null
        )->shouldBeCalled();
        $this->configuration->setBackgroundImage('background.png')->shouldBeCalled();
        $this->configuration->setColors(
            '#CCCCC2',
            '#DDDDD2',
            '#FFFFFF',
            '#AAAAAA',
            '#BBBBBB',
            '#EEEEE2',
            '#2F2F2F',
            '#2D2D2D',
            '#FFFFFF'
        )->shouldBeCalled();

        $this->event->setAssetPath('/path/asset/main.css')->shouldBeCalled();
        $this->eventRepository->set($this->event->reveal())->shouldBeCalled();

        $updateDesign = new UpdateDesign($this->event->reveal());
        $file1 = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'jpeg'])
            ->getMock();
        $file2 = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'png'])
            ->getMock();
        $file3 = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'jpeg'])
            ->getMock();
        $file4 = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'png'])
            ->getMock();

        $updateDesign->localizedImages = [
            'fr' => [
                'logo' => $file1,
                'mobileLogo' => null,
                'notificationImage' => $file4
            ],
            'en' => [
                'logo' => null,
                'mobileLogo' => $file2,
                'notificationImage' => null
            ],
        ];

        $updateDesign->backgroundImage = $file3;
        $updateDesign->backgroundColor = '#EEEEE2';
        $updateDesign->leftColor = '#CCCCC2';
        $updateDesign->rightColor = '#DDDDD2';
        $updateDesign->headerButtonRightColor = '#2D2D2D';

        $handler = new UpdateDesignHandler(
            $this->eventRepository->reveal(),
            $this->fileStorage->reveal(),
            $this->removeImageHandler->reveal(),
            $this->guidelineGenerator->reveal()
        );

        $handler->handle($updateDesign);
    }

    public function testHandleRemoveBackgroundImage(): void
    {
        $this->event->getLocales()->willReturn(['fr', 'en']);
        $this->configuration->getHeaderLeftColor()->willReturn('#AAAAAA');
        $this->configuration->getHeaderRightColor()->willReturn('#BBBBBB');
        $this->configuration->getLeftColor()->willReturn('#CCCCCC');
        $this->configuration->getRightColor()->willReturn('#DDDDDD');
        $this->configuration->getBackgroundColor()->willReturn('#EEEEEE');
        $this->configuration->getTextColor()->willReturn('#FFFFFF');
        $this->configuration->getHeaderButtonLeftColor()->willReturn('#2F2F2F');
        $this->configuration->getHeaderButtonRightColor()->willReturn('#2F2F2F');
        $this->configuration->getHeaderButtonTextColor()->willReturn('#FFFFFF');
        $this->configuration->getBackgroundImage()->willReturn('background.png');

        $this->event->getLocalizedLogo('fr')->willReturn('logoFr.png');
        $this->event->getLocalizedLogoExtension('fr')->willReturn('png');
        $this->event->getLocalizedMobileLogo('fr')->willReturn(null);
        $this->event->getLocalizedMobileLogoExtension('fr')->willReturn(null);

        $this->event->getLocalizedLogo('en')->willReturn('logoEn.jpeg');
        $this->event->getLocalizedLogoExtension('en')->willReturn('jpeg');
        $this->event->getLocalizedMobileLogo('en')->willReturn('mobileLogoEn.jpeg');
        $this->event->getLocalizedMobileLogoExtension('en')->willReturn('jpeg');

        $this->event->getLocalizedNotificationImage('fr')->willReturn(null);
        $this->event->getLocalizedNotificationImageExtension('fr')->willReturn(null);

        $this->event->getLocalizedNotificationImage('en')->willReturn(null);
        $this->event->getLocalizedNotificationImageExtension('en')->willReturn(null);

        $this->guidelineGenerator->generate($this->event->reveal())->shouldBeCalled()->willReturn('/path/asset/main.css');
        $this->removeImageHandler->handle(new RemoveImage($this->event->reveal()))->shouldBeCalled();

        $this->event->updateLocalizedLogos(
            'fr',
            'logoFr.png',
            'png',
            null,
            null,
             null,
            null
        )->shouldBeCalled();
        $this->event->updateLocalizedLogos(
            'en',
            'logoEn.jpeg',
            'jpeg',
            'mobileLogoEn.jpeg',
            'jpeg',
            null,
            null
        )->shouldBeCalled();
        $this->configuration->setColors(
            '#CCCCC2',
            '#DDDDD2',
            '#FFFFFF',
            '#AAAAAA',
            '#BBBBBB',
            '#EEEEE2',
            '#2F2F2F',
            '#2D2D2D',
            '#FFFFFF'
        )->shouldBeCalled();

        $this->event->setAssetPath('/path/asset/main.css')->shouldBeCalled();
        $this->eventRepository->set($this->event->reveal())->shouldBeCalled();

        $updateDesign = new UpdateDesign($this->event->reveal());

        $updateDesign->localizedImages = [
            'fr' => [
                'logo' => null,
                'mobileLogo' => null,
                'notificationImage' => null
            ],
            'en' => [
                'logo' => null,
                'mobileLogo' => null,
                'notificationImage' => null
            ],
        ];

        $updateDesign->backgroundColor = '#EEEEE2';
        $updateDesign->leftColor = '#CCCCC2';
        $updateDesign->rightColor = '#DDDDD2';
        $updateDesign->headerButtonRightColor = '#2D2D2D';
        $updateDesign->removeBackgroundImage = true;

        $handler = new UpdateDesignHandler(
            $this->eventRepository->reveal(),
            $this->fileStorage->reveal(),
            $this->removeImageHandler->reveal(),
            $this->guidelineGenerator->reveal()
        );

        $handler->handle($updateDesign);
    }

    public function testHandleColorsNotUpdated(): void
    {
        $this->event->getLocales()->willReturn(['fr', 'en']);
        $this->configuration->getHeaderLeftColor()->willReturn('#AAAAAA');
        $this->configuration->getHeaderRightColor()->willReturn('#BBBBBB');
        $this->configuration->getLeftColor()->willReturn('#CCCCCC');
        $this->configuration->getRightColor()->willReturn('#DDDDDD');
        $this->configuration->getBackgroundColor()->willReturn('#EEEEEE');
        $this->configuration->getTextColor()->willReturn('#FFFFFF');
        $this->configuration->getHeaderButtonLeftColor()->willReturn('#2F2F2F');
        $this->configuration->getHeaderButtonRightColor()->willReturn('#2F2F2F');
        $this->configuration->getHeaderButtonTextColor()->willReturn('#FFFFFF');
        $this->configuration->getBackgroundImage()->willReturn('background.png');

        $this->event->getLocalizedLogo('fr')->willReturn('logoFr.png');
        $this->event->getLocalizedLogoExtension('fr')->willReturn('png');
        $this->event->getLocalizedMobileLogo('fr')->willReturn(null);
        $this->event->getLocalizedMobileLogoExtension('fr')->willReturn(null);

        $this->event->getLocalizedLogo('en')->willReturn('logoEn.jpeg');
        $this->event->getLocalizedLogoExtension('en')->willReturn('jpeg');
        $this->event->getLocalizedMobileLogo('en')->willReturn('mobileLogoEn.jpeg');
        $this->event->getLocalizedMobileLogoExtension('en')->willReturn('jpeg');

        $this->event->getLocalizedNotificationImage('fr')->willReturn(null);
        $this->event->getLocalizedNotificationImageExtension('fr')->willReturn(null);

        $this->event->getLocalizedNotificationImage('en')->willReturn(null);
        $this->event->getLocalizedNotificationImageExtension('en')->willReturn(null);

        $this->guidelineGenerator->generate($this->event->reveal())->shouldNotBeCalled();
        $this->removeImageHandler->handle(new RemoveImage($this->event->reveal()))->shouldNotBeCalled();

        $this->event->updateLocalizedLogos(
            'fr',
            'logoFr.png',
            'png',
            null,
            null,
            null,
            null
        )->shouldBeCalled();
        $this->event->updateLocalizedLogos(
            'en',
            'logoEn.jpeg',
            'jpeg',
            'mobileLogoEn.jpeg',
            'jpeg',
            null,
            null
        )->shouldBeCalled();
        $this->configuration->setColors(
            '#CCCCCC',
            '#DDDDDD',
            '#FFFFFF',
            '#AAAAAA',
            '#BBBBBB',
            '#EEEEEE',
            '#2F2F2F',
            '#2F2F2F',
            '#FFFFFF'
        )->shouldBeCalled();

        $this->event->setAssetPath('/new/asset/path.css')->shouldNotBeCalled();
        $this->eventRepository->set($this->event->reveal())->shouldBeCalled();

        $updateDesign = new UpdateDesign($this->event->reveal());

        $updateDesign->localizedImages = [
            'fr' => [
                'logo' => null,
                'mobileLogo' => null,
                'notificationImage' => null
            ],
            'en' => [
                'logo' => null,
                'mobileLogo' => null,
                'notificationImage' => null
            ],
        ];

        $handler = new UpdateDesignHandler(
            $this->eventRepository->reveal(),
            $this->fileStorage->reveal(),
            $this->removeImageHandler->reveal(),
            $this->guidelineGenerator->reveal()
        );

        $handler->handle($updateDesign);
    }
}
