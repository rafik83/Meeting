<?php

namespace Proximum\Vimeet\Tests\Application\Command\Sheet;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Command\Sheet\BatchCatalog;
use Proximum\Vimeet\Application\Command\Sheet\BatchCatalogHandler;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\MeetingRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Adapter\BatchJobQueue\BatchCatalogJobQueue;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BatchCatalogHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $date  = new \DateTime();

        $admin = new Admin('email@email.com', 'toto', 'tata', 'fr', 'truc', 'muche', 'ROLE_SUPER_ADMIN',
            new \DateTime());

        // actual sheet
        $user1  = new User('test@test.com', 'salt', 'password', 'fr');
        $user2  = new User('test@test.com', 'salt', 'password', 'fr');
        $user3  = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet1 = new Sheet($event, $type, [], $user1, $date);
        $sheet2 = new Sheet($event, $type, [], $user2, $date);
        $sheet3 = new Sheet($event, $type, [], $user3, $date);

        // expected sheet
        $expectedSheet1 = new Sheet($event, $type, [], $user1, $date);
        $expectedSheet1->setInCatalog(true);
        $expectedSheet1->setInCatalogAt($date);

        $expectedSheet2 = new Sheet($event, $type, [], $user2, $date);
        $expectedSheet2->setInCatalog(true);
        $expectedSheet2->setInCatalogAt($date);

        $expectedSheet3 = new Sheet($event, $type, [], $user3, $date);
        $expectedSheet3->setInCatalog(true);
        $expectedSheet3->setInCatalogAt($date);

        $reflection  = new \ReflectionClass(Sheet::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($sheet1, 1);
        $property->setValue($sheet2, 2);
        $property->setValue($sheet3, 3);
        $property->setAccessible(false);

        // Mock
        $sheetRepository      = $this->prophesize(SheetRepositoryInterface::class);
        $meetingRepository    = $this->prophesize(MeetingRepositoryInterface::class);
        $sheetInfoGuesser     = $this->prophesize(SheetInfoGuesser::class);
        $batchJobQueue        = $this->prophesize(BatchCatalogJobQueue::class);

        $sheetRepository
            ->getSheetsById([1, 2, 3])
            ->shouldBeCalled()
            ->willReturn([
                1 => $sheet1,
                2 => $sheet2,
                3 => $sheet3,
            ]);

        $meetingRepository->countMeetingsOfSheetByIds([1, 2, 3])->shouldBeCalled();

        $sheetRepository->updateInCatalogBySheetsId([1, 2, 3], true)->shouldBeCalled();

        $batchJobQueue->createJob([1, 2, 3], $admin, ['state' => BatchCatalogHandler::ADD_CATALOG])->shouldBeCalled();

        // Command
        $command = new BatchCatalog([1, 2, 3], true, $admin);
        $handler = new BatchCatalogHandler(
            $sheetRepository->reveal(),
            $meetingRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $batchJobQueue->reveal()
        );

        $result = $handler->handle($command);
        $this->assertEquals(3, $result->count);
    }

    public function testHandleWithIgnoredSheets()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $date  = new \DateTime();
        $dateold = new \DateTime('2016-10-12 10:10');

        $admin = new Admin('email@email.com', 'toto', 'tata', 'fr', 'truc', 'muche', 'ROLE_SUPER_ADMIN',
            new \DateTime());

        // actual sheet
        $user1  = new User('test@test.com', 'salt', 'password', 'fr');
        $user2  = new User('test@test.com', 'salt', 'password', 'fr');
        $user3  = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet1 = new Sheet($event, $type, [], $user1, new \DateTime());
        $sheet1->setInCatalog(true);
        $sheet1->setInCatalogAt($dateold);
        $sheet2 = new Sheet($event, $type, [], $user2, new \DateTime());
        $sheet2->setInCatalog(true);
        $sheet2->setInCatalogAt($dateold);
        $sheet3 = new Sheet($event, $type, [], $user3, new \DateTime());
        $sheet3->setInCatalog(true);
        $sheet3->setInCatalogAt($dateold);

        $reflection  = new \ReflectionClass(Sheet::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($sheet1, 1);
        $property->setValue($sheet2, 2);
        $property->setValue($sheet3, 3);
        $property->setAccessible(false);

        // Mock
        $sheetRepository   = $this->prophesize(SheetRepositoryInterface::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $sheetInfoGuesser  = $this->prophesize(SheetInfoGuesser::class);
        $batchJobQueue     = $this->prophesize(BatchCatalogJobQueue::class);

        $sheetRepository
            ->getSheetsById([1 => 1, 2 => 2, 3 => 3])
            ->shouldBeCalled()
            ->willReturn([
                1 => $sheet1,
                2 => $sheet2,
                3 => $sheet3,
            ]);

        $meetingRepository
            ->countMeetingsOfSheetByIds([1 => 1, 2 => 2, 3 => 3])
            ->shouldBeCalled()
            ->willReturn([
                1 => 0,
                2 => 2,
                3 => 0,
            ]);

        $sheetInfoGuesser->guessSheetTitle($sheet2, 'fr')->shouldBeCalled()->willReturn('SheetName');

        $sheetRepository->updateInCatalogBySheetsId([1 => 1, 3 => 3], false)->shouldBeCalled();

        $batchJobQueue->createJob([1 => 1, 3 => 3], $admin, ['state' => BatchCatalogHandler::REMOVE_CATALOG])->shouldBeCalled();

        // Command
        $command = new BatchCatalog([1 => 1, 2 => 2, 3 => 3], false, $admin);
        $handler = new BatchCatalogHandler(
            $sheetRepository->reveal(),
            $meetingRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $batchJobQueue->reveal()
        );

        $result = $handler->handle($command);
        $this->assertEquals(3, $result->count);
        $this->assertEquals('SheetName', $result->ignoredSheetsMessage);
    }

    public function testHandleAddToCatalogWithIgnoredSheets()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);
        $date  = new \DateTime();
        $dateold = new \DateTime('2016-10-12 10:10');

        $admin = new Admin('email@email.com', 'toto', 'tata', 'fr', 'truc', 'muche', 'ROLE_SUPER_ADMIN',
            new \DateTime());

        // actual sheet
        $user1  = new User('test@test.com', 'salt', 'password', 'fr');
        $user2  = new User('test@test.com', 'salt', 'password', 'fr');
        $sheet1 = new Sheet($event, $type, [], $user1, new \DateTime());
        $sheet1->setInCatalog(false);
        $sheet1->setEnable(false);
        $sheet1->setInCatalogAt($dateold);
        $sheet2 = new Sheet($event, $type, [], $user2, new \DateTime());
        $sheet2->setInCatalog(false);
        $sheet2->setInCatalogAt($dateold);

        $reflection  = new \ReflectionClass(Sheet::class);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($sheet1, 1);
        $property->setValue($sheet2, 2);
        $property->setAccessible(false);

        // Mock
        $sheetRepository   = $this->prophesize(SheetRepositoryInterface::class);
        $meetingRepository = $this->prophesize(MeetingRepositoryInterface::class);
        $sheetInfoGuesser  = $this->prophesize(SheetInfoGuesser::class);
        $batchJobQueue     = $this->prophesize(BatchCatalogJobQueue::class);

        $sheetRepository->getSheetsById([1 => 1, 2 => 2])->shouldBeCalled()->willReturn([
            1 => $sheet1,
            2 => $sheet2,
        ]);
        $sheetInfoGuesser->guessSheetTitle($sheet1, 'fr')->shouldBeCalled()->willReturn('SheetName');

        $sheetRepository->updateInCatalogBySheetsId([2 => 2], true)->shouldBeCalled();

        $batchJobQueue->createJob([2 => 2], $admin, ['state' => BatchCatalogHandler::ADD_CATALOG])->shouldBeCalled();

        // Command
        $command = new BatchCatalog([1 => 1, 2 => 2], true, $admin);
        $handler = new BatchCatalogHandler(
            $sheetRepository->reveal(),
            $meetingRepository->reveal(),
            $sheetInfoGuesser->reveal(),
            $batchJobQueue->reveal()
        );

        $result = $handler->handle($command);
        $this->assertEquals(2, $result->count);
        $this->assertEquals('SheetName', $result->ignoredSheetsMessage);
    }
}
