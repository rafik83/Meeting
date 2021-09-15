<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Command\Happening\Update;
use Proximum\Vimeet\Application\Command\Happening\UpdateHandler;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Happening\DatesUpdated;
use Proximum\Vimeet\Application\Event\Happening\TypesUpdated;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Happening\CategoryTranslation;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UpdateHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = EventFactory::createEvent();
        $event->setLocales(['fr', 'en'], 'fr');

        $begin = new \DateTime('2016-01-27 00:00:00');
        $end = new \DateTime('2016-01-29 00:00:00');

        $newBegin = new \DateTime('2016-01-27 10:00:00');
        $newEnd = new \DateTime('2016-01-29 19:00:00');

        // Current
        $category = new Category($event, 'picto1', 0, '#AABB56', '#123456');
        $catTranslation1 = new CategoryTranslation($category, 'fr', 'truc');
        $catTranslation2 = new CategoryTranslation($category, 'en', 'trac');
        $category->setTranslation($catTranslation1);
        $category->setTranslation($catTranslation2);
        $previousType = $this->prophesize(Type::class);

        $happening = new Happening($event, $begin, $end, $category, [$previousType->reveal()], true, 10, 'toto');
        $happeningTranslation = new Happening\HappeningTranslation($happening, 'fr', 'truc', 'bidule');
        $happeningTranslation2 = new Happening\HappeningTranslation($happening, 'en', 'trac', 'machin', '/path/currentWebinarHeaderImageEn.jpg', '/path/webinarWaitingMediaEn.mp4', 'video/mp4');

        $happening->setTranslation($happeningTranslation);
        $happening->setTranslation($happeningTranslation2);

        $newCategory = new Category($event, 'picto3', 0, '#123123', '#456456');
        $newCatTranslation1 = new CategoryTranslation($newCategory, 'fr', 'trec');
        $newCatTranslation2 = new CategoryTranslation($newCategory, 'en', 'troc');
        $newCategory->setTranslation($newCatTranslation1);
        $newCategory->setTranslation($newCatTranslation2);
        $newType = $this->prophesize(Type::class);

        $webinarHeaderImageEn = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), 'header_image.png'), 'png'])
            ->getMock();
        $webinarHeaderImageEn->method('getClientOriginalName')->willReturn('webinarHeaderImageEn.png');

        $webinarWaitingMediaEn = $this
            ->getMockBuilder(UploadedFile::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), 'waiting_image.png'), 'image/png'])
            ->getMock();
        $webinarWaitingMediaEn->method('getClientOriginalName')->willReturn('webinarWaitingMediaEn.png');
        $webinarWaitingMediaEn->method('getMimeType')->willReturn('image/png');

        // Expected
        $expectedSubEvent = new Happening(
            $event,
            $newBegin,
            $newEnd,
            $newCategory,
            [$newType->reveal()],
            false,
            null,
            'titi',
            true,
            false,
            false,
            null,
            true,
            true,
            false,
            true,
            false
        );
        $expectedTranslation = new Happening\HappeningTranslation($expectedSubEvent, 'fr', 'test', 'ok');
        $expectedTranslation2 = new Happening\HappeningTranslation(
            $expectedSubEvent,
            'en',
            'tset',
            'ko',
            '/path/webinarHeaderImageEn.png',
            '/path/webinarWaitingMediaEn.png',
            'image'
        );

        $expectedSubEvent->setTranslation($expectedTranslation);
        $expectedSubEvent->setTranslation($expectedTranslation2);

        // Mock
        $happeningRepository = $this->prophesize(HappeningRepositoryInterface::class);
        $eventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $happeningRepository->set($expectedSubEvent)->shouldBeCalled();
        $eventDispatcher->dispatch(Events::HAPPENING_TYPES_UPDATED, new TypesUpdated($happening))->shouldBeCalled();
        $eventDispatcher->dispatch(Events::HAPPENING_DATES_UPDATED, new DatesUpdated($happening))->shouldBeCalled();

        // Command
        $update = new Update($happening);
        $update->questionAllowed  = false;
        $update->category = $newCategory;
        $update->begin = $newBegin;
        $update->end = $newEnd;
        $update->limitParticipant = null;
        $update->types = [
            $newType->reveal(),
        ];
        $update->translations = [
            'fr' => [
                'title' => 'test',
                'description' => 'ok',
                'currentWebinarHeaderImage' => null,
                'currentWebinarWaitingMediaFile' => null,
                'currentWebinarWaitingMediaType' => null,
                'webinarHeaderImage' => null,
                'webinarWaitingMedia' => null,
            ],
            'en' => [
                'title' => 'tset',
                'description' => 'ko',
                'currentWebinarHeaderImage' => '/path/currentWebinarHeaderImageEn.jpg',
                'currentWebinarWaitingMediaFile' => '/path/currentWebinarWaitingMediaEn.mp4',
                'currentWebinarWaitingMediaType' => 'video/mp4',
                'webinarHeaderImage' => $webinarHeaderImageEn,
                'webinarWaitingMedia' => $webinarWaitingMediaEn,
            ],
        ];
        $update->invitationCode = 'titi';
        $update->webinarRecorded = true;
        $update->happeningType = 'webinar';

        /** @var ObjectProphecy */
        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $fileStorage
            ->remove('/path/currentWebinarHeaderImageEn.jpg')
            ->shouldBeCalled()
        ;
        $fileStorage
            ->upload(Argument::which('getClientOriginalName', 'webinarHeaderImageEn.png'))
            ->shouldBeCalled()
            ->willReturn('/path/webinarHeaderImageEn.png')
        ;
        $fileStorage
            ->remove('/path/currentWebinarWaitingMediaEn.mp4')
            ->shouldBeCalled()
        ;
        $fileStorage
            ->upload(Argument::which('getClientOriginalName', 'webinarWaitingMediaEn.png'))
            ->shouldBeCalled()
            ->willReturn('/path/webinarWaitingMediaEn.png')
        ;

        $handler = new UpdateHandler(
            $happeningRepository->reveal(), $fileStorage->reveal(), $eventDispatcher->reveal()
        );
        $handler->handle($update);
    }
}
