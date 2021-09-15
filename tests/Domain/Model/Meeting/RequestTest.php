<?php

namespace Proximum\Vimeet\Tests\Domain\Model\Meeting;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class RequestTest extends TestCase
{
    public function testHasNoPreferences()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $sheetFrom = $this->prophesize(Sheet::class);
        $sheetFromParticipant1 = $this->prophesize(Participant::class);

        $sheetTo = $this->prophesize(Sheet::class);
        $sheetToParticipant1 = $this->prophesize(Participant::class);

        $sheetOtherLinkedToSheetTo = $this->prophesize(Sheet::class);
        $sheetFrom->hasLinkedSheet($sheetOtherLinkedToSheetTo->reveal())->shouldBeCalled()->willReturn(false);
        $sheetTo->hasLinkedSheet($sheetOtherLinkedToSheetTo->reveal())->shouldBeCalled()->willReturn(true);

        $request1 = new Request(
            $sheetFrom->reveal(),
            [],
            $sheetTo->reveal(),
            [],
            new \DateTime(),
            $user->reveal(),
            $event->reveal()
        );
        $this->assertTrue($request1->hasNoPreference($sheetFrom->reveal()));
        $this->assertTrue($request1->hasNoPreference($sheetTo->reveal()));
        $this->assertTrue($request1->hasNoPreference($sheetOtherLinkedToSheetTo->reveal()));

        $request2 = new Request(
            $sheetFrom->reveal(),
            [$sheetFromParticipant1->reveal()],
            $sheetTo->reveal(),
            [$sheetToParticipant1->reveal()],
            new \DateTime(),
            $user->reveal(),
            $event->reveal()
        );
        $this->assertFalse($request2->hasNoPreference($sheetFrom->reveal()));
        $this->assertFalse($request2->hasNoPreference($sheetTo->reveal()));
        $this->assertFalse($request2->hasNoPreference($sheetOtherLinkedToSheetTo->reveal()));
    }
}
