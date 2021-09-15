<?php


namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\User;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Application\Adapter\SerializerAdapterInterface;
use Proximum\Vimeet\Application\ThirdParty\LENI\User\Import\Import;
use Proximum\Vimeet\Application\ThirdParty\LENI\User\Import\ImportUserView;
use Proximum\Vimeet\Application\ThirdParty\LENI\User\Import\ImportHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

class ImportHandlerTest extends TestCase
{
    public function testHandle()
    {
        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $fileStorage = $this->prophesize(FileStorageInterface::class);
        $serializer = $this->prophesize(SerializerAdapterInterface::class);
        $event = $this->prophesize(Event::class);
        $event->getId()->willReturn(666);
        $date = new \DateTime();

        $user1 = $this->prophesize(User::class);
        $user1->getId()->willReturn(85201);

        $user2 = $this->prophesize(User::class);
        $user2->getId()->willReturn(34891);
        $user2ExtraData = $this->prophesize(ExtraData::class);

        $userRepository = $this->prophesize(UserRepositoryInterface::class);
        $userRepository->findWithEnabledSheetByEvent($event->reveal())->willReturn([$user1->reveal(), $user2->reveal()]);
        $userRepository->findOwnersWithEnabledSheetByEvent($event->reveal())->willReturn([$user1->reveal()]);

        $data = "userId;leniUserId;\n92125;x-y-z;\n34891;z-a-b;\n85201;d-b-a;";
        $fileStorage->getContents(Argument::any())->willReturn($data);

        $importUserView = $this->prophesize(ImportUserView::class);
        $importUserView->leniUserId = 'd-b-a';
        $importUserView->userId = 85201;

        $importUserView2 = $this->prophesize(ImportUserView::class);
        $importUserView2->leniUserId = 'z-a-b';
        $importUserView2->userId = 34891;

        $importUserView3 = $this->prophesize(ImportUserView::class);
        $importUserView3->leniUserId = 'x-y-z';
        $importUserView3->userId = 92125;

        $serializer
            ->deserialize($data, ImportUserView::class.'[]', 'csv',
                [
                    'csv_delimiter' => ';',
                    'event' => $event->reveal(),
                ])
            ->shouldBeCalled()
            ->willReturn([$importUserView->reveal(), $importUserView2->reveal(), $importUserView3->reveal()]);

        // Add extra data user
        $extraDataRepository->add(new ExtraData(
            $user1->reveal(),
            $event->reveal(),
            Type::LENI_USER_ID,
            'd-b-a',
            $date
        ))->shouldBeCalled();

        $extraDataRepository->getExtraDataForEventIdAndNameIndexedByUserId(666, 'leni_user_id')->willReturn([34891 => $user2ExtraData->reveal()]);
        // Update extra data user
        $extraDataRepository->set($user2ExtraData->reveal())->shouldBeCalled();

        $handler = new ImportHandler($extraDataRepository->reveal(), $fileStorage->reveal(), $serializer->reveal(), $userRepository->reveal(), $date);
        $command = new Import($event->reveal());
        $command->file = 'file.csv';
        $result = $handler->handle($command);

        $this->assertEquals(1, $result->countAddedUsers());
        $this->assertEquals(1, $result->countUpdatedUsers());
    }
}
