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

        $event = $this->prophesize(Event::class);
        $event->getLocales()->shouldBeCalled()->willReturn(['fr', 'en']);

        $visioSettings = new VisioSettings($event->reveal());
        $visioSettings->updateTranslation('fr', '/test_fr.jpeg');
        $visioSettings->updateTranslation('en', '/test_en.jpeg');

        $updateVisioSettings = new UpdateVisioSettings(
            $event->reveal(),
            $visioSettings
        );
        $updateVisioSettings->localizedVisioSettings = [
            'fr' => [
                'header' => $image,
                'removeHeader' => false,
            ],
            'en' => [
                'header' => null,
                'removeHeader' => true,
            ]
        ];

        $visioSettingsRepository = $this->prophesize(VisioSettingsRepositoryInterface::class);
        $fileStorage = $this->prophesize(FileStorageInterface::class);

        $fileStorage->remove('/test_fr.jpeg')->shouldBeCalled();
        $fileStorage->remove('/test_en.jpeg')->shouldBeCalled();
        $fileStorage->upload($image)->shouldBeCalled()->willReturn('/new_test_fr.jpeg');

        $expected = new VisioSettings($event->reveal());
        $expected->updateTranslation('fr', '/new_test_fr.jpeg');

        $visioSettingsRepository->update($expected)->shouldBeCalled();

        $handler = new UpdateVisioSettingsHandler(
            $visioSettingsRepository->reveal(),
            $fileStorage->reveal()
        );

        $handler->handle($updateVisioSettings);
    }
}
