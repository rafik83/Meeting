<?php

namespace Proximum\Vimeet\Tests\Domain\User\Sheet;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\User\Sheet\FirstParticipantSheetOfUserGetter;
use PHPUnit\Framework\TestCase;

class FirstParticipantSheetOfUserGetterTest extends TestCase
{
    public function testGetFirstParticipantSheet(): void
    {
        $user = $this->prophesize(User::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet3 = $this->prophesize(Sheet::class);
        $participant1 = $this->prophesize(Participant::class);
        $participant2 = $this->prophesize(Participant::class);

        $sheet3->getId()->shouldBeCalled()->willReturn(13);

        $participant1->getId()->shouldBeCalled()->willReturn(999);
        $participant2->getId()->shouldBeCalled()->willReturn(111);
        $sheet1->getUserParticipant($user->reveal())->shouldBeCalled()->willReturn($participant1);
        $sheet2->getUserParticipant($user->reveal())->shouldBeCalled()->willReturn(null);
        $sheet3->getUserParticipant($user->reveal())->shouldBeCalled()->willReturn($participant2);

        $sheets = [
            $sheet1->reveal(),
            $sheet2->reveal(),
            $sheet3->reveal(),
        ];

        $firstParticipantSheetOfUserGetter = new FirstParticipantSheetOfUserGetter();
        $result = $firstParticipantSheetOfUserGetter->getFirstParticipantSheet($user->reveal(), $sheets);

        $this->assertInstanceOf(Sheet::class, $result);
        $this->assertEquals(13, $result->getId());
    }

    public function testGetNoSheet(): void
    {
        $user = $this->prophesize(User::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->getUserParticipant($user->reveal())->shouldBeCalled()->willReturn(null);

        $sheets = [
            $sheet2->reveal(),
        ];

        $firstParticipantSheetOfUserGetter = new FirstParticipantSheetOfUserGetter();
        $result = $firstParticipantSheetOfUserGetter->getFirstParticipantSheet($user->reveal(), $sheets);

        $this->assertNull($result);
    }
}
