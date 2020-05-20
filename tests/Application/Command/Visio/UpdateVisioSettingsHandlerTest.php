<?php

namespace Proximum\Vimeet\Tests\Application\Command\Visio;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Visio\UpdateVisioSettings;
use Proximum\Vimeet\Application\Command\Visio\UpdateVisioSettingsHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Visio\VisioSettings;
use Proximum\Vimeet\Domain\Repository\Visio\VisioSettingsRepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UpdateVisioSettingsHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $image = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'jpeg'])
            ->getMock()
        ;
        $sound = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'wav'])
            ->getMock()
        ;
        $header = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'png'])
            ->getMock()
        ;

        $event = $this->prophesize(Event::class);
        $event->getLocales()->shouldBeCalled()->willReturn(['fr', 'en']);

        $visioSettings = new VisioSettings($event->reveal());
        $visioSettings->updateTranslation(
            'fr',
            '/old_header_fr.jpeg',
            '/old_sound_fr.wav',
            null,
            null
        );
        $visioSettings->updateTranslation(
            'en',
            '/old_header_en.jpeg',
            null,
            '/old_end_image_en.jpeg',
            'old message in en'
        );

        $updateVisioSettings = new UpdateVisioSettings(
            $event->reveal(),
            $visioSettings
        );
        $updateVisioSettings->localizedVisioSettings = [
            'fr' => [
                'header' => $header,
                'removeHeader' => false,
                'endSound' => null,
                'removeEndSound' => false,
                'endImage' => $image,
                'removeEndImage' => false,
                'endMessage' => 'end message in fr',
            ],
            'en' => [
                'header' => null,
                'removeHeader' => true,
                'endSound' => $sound,
                'removeEndSound' => false,
                'endImage' => null,
                'removeEndImage' => true,
                'endMessage' => 'end message in en',
            ]
        ];

        $visioSettingsRepository = $this->prophesize(VisioSettingsRepositoryInterface::class);
        $fileStorage = $this->prophesize(FileStorageInterface::class);

        $fileStorage->remove('/old_header_fr.jpeg')->shouldBeCalled();
        $fileStorage->remove('/old_header_en.jpeg')->shouldBeCalled();
        $fileStorage->remove('/old_end_image_en.jpeg')->shouldBeCalled();
        $fileStorage->upload($header)->shouldBeCalled()->willReturn('/header_fr.jpeg');
        $fileStorage->upload($sound)->shouldBeCalled()->willReturn('/sound_en.wav');
        $fileStorage->upload($image)->shouldBeCalled()->willReturn('/end_image_fr.jpeg');

        $expected = new VisioSettings($event->reveal());
        $expected->updateTranslation(
            'fr',
            '/header_fr.jpeg',
            '/old_sound_fr.wav',
            '/end_image_fr.jpeg',
            'end message in fr'
        );
        $expected->updateTranslation(
            'en',
            null,
            '/sound_en.wav',
            null,
            'end message in en'
        );

        $visioSettingsRepository->update($expected)->shouldBeCalled();

        $handler = new UpdateVisioSettingsHandler(
            $visioSettingsRepository->reveal(),
            $fileStorage->reveal()
        );

        $handler->handle($updateVisioSettings);
    }
}
