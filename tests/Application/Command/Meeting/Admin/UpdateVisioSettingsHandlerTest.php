<?php

namespace Proximum\Vimeet\Tests\Application\Command\Meeting\Admin;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateVisioSettings;
use Proximum\Vimeet\Application\Command\Meeting\Admin\UpdateVisioSettingsHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Repository\EventRepository;
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

        $updateVisioSettings = new UpdateVisioSettings($event->reveal());
        $updateVisioSettings->localizedVisioHeaders = [
            'fr' => [
                'header' => $image,
                'removeHeader' => false,
            ],
            'en' => [
                'header' => null,
                'removeHeader' => true,
            ]
        ];

        $eventRepository = $this->prophesize(EventRepositoryInterface::class);
        $fileStorage = $this->prophesize(FileStorageInterface::class);

        $event->getLocalizedVisioHeader('fr')->shouldBeCalled()->willReturn('test_fr.jpeg');
        $event->getLocalizedVisioHeader('en')->shouldBeCalled()->willReturn('test_en.jpeg');

        $fileStorage->remove('test_fr.jpeg')->shouldBeCalled();
        $fileStorage->remove('test_en.jpeg')->shouldBeCalled();
        $fileStorage->upload($image)->shouldBeCalled()->willReturn('new_test_fr.jpeg');

        $event->updateLocalizedVisioHeader('fr', 'new_test_fr.jpeg')->shouldBeCalled();
        $event->updateLocalizedVisioHeader('en', null)->shouldBeCalled();

        $eventRepository->set($event->reveal())->shouldBeCalled();

        $handler = new UpdateVisioSettingsHandler(
            $eventRepository->reveal(),
            $fileStorage->reveal()
        );

        $handler->handle($updateVisioSettings);
    }
}
