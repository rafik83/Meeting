<?php

namespace Proximum\Vimeet\Tests\Application\Command\User\Phone;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\SMSBlackListInterface;
use Proximum\Vimeet\Application\Command\User\Phone\UpdateBlackList;
use Proximum\Vimeet\Application\Command\User\Phone\UpdateBlackListHandler;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;

class UpdateBlackListHandlerTest extends TestCase
{
    public function testHandle()
    {
        $SMSBlackList = $this->prophesize(SMSBlackListInterface::class);
        $SMSBlackList->getBlackList()->shouldBeCalled()->willReturn(['+33123213123', '+345656565656']);
        $userEventPhoneRepository = $this->prophesize(UserEventPhoneRepositoryInterface::class);
        $userEventPhoneRepository->setIntoBlackList(['+33123213123', '+345656565656'])->shouldBeCalled();
        $userEventPhoneRepository->unsetFromBlackList(['+33123213123', '+345656565656'])->shouldBeCalled();

        $updateBlackListHandler = new UpdateBlackListHandler($SMSBlackList->reveal(), $userEventPhoneRepository->reveal());
        $updateBlackListHandler->handle(new UpdateBlackList());
    }
}
