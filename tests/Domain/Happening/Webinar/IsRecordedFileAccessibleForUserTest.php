<?php

namespace Proximum\Vimeet\Tests\Domain\Happening\Webinar;

use DateTime;
use Proximum\Vimeet\Domain\Happening\Webinar\IsRecordedFileAccessibleForUser;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class IsRecordedFileAccessibleForUserTest extends TestCase
{
    public function testIsSatisfiedByNotRecorded(): void
    {
        $happening = $this->prophesize(Happening::class);
        $user = $this->prophesize(User::class);
        $date = new DateTime();

        $happening->isWebinarRecorded()->willReturn(false);

        $isRecordedFileAccessibleForUser = new IsRecordedFileAccessibleForUser($date);

        $this->assertFalse($isRecordedFileAccessibleForUser->isSatisfiedBy($happening->reveal(), $user->reveal()));
    }

    public function testIsSatisfiedByDateTooOld(): void
    {
        $happening = $this->prophesize(Happening::class);
        $user = $this->prophesize(User::class);
        $date = new DateTime();
        $end = new DateTime('2020-01-01 10:00:00.000');

        $happening->isWebinarRecorded()->willReturn(true);
        $happening->getEnd()->willReturn($end);

        $isRecordedFileAccessibleForUser = new IsRecordedFileAccessibleForUser($date);

        $this->assertFalse($isRecordedFileAccessibleForUser->isSatisfiedBy($happening->reveal(), $user->reveal()));
    }

    public function testIsSatisfiedByUserNotSpeaker(): void
    {
        $happening = $this->prophesize(Happening::class);
        $user = $this->prophesize(User::class);
        $otherUser = $this->prophesize(User::class);
        $date = new DateTime('2020-01-03 10:00:00.000');
        $end = new DateTime('2020-01-01 10:00:00.000');

        $speaker1 = $this->prophesize(Happening\Speaker::class);
        $speaker2 = $this->prophesize(Happening\Speaker::class);
        $speaker1->getUser()->willReturn(null);
        $speaker2->getUser()->willReturn($otherUser->reveal());

        $happening->isWebinarRecorded()->willReturn(true);
        $happening->getEnd()->willReturn($end);
        $happening->getSpeakers()->willReturn([$speaker1, $speaker2]);

        $isRecordedFileAccessibleForUser = new IsRecordedFileAccessibleForUser($date);

        $this->assertFalse($isRecordedFileAccessibleForUser->isSatisfiedBy($happening->reveal(), $user->reveal()));
    }

    public function testIsSatisfiedByNoFile(): void
    {
        $happening = $this->prophesize(Happening::class);
        $user = $this->prophesize(User::class);
        $otherUser = $this->prophesize(User::class);
        $date = new DateTime('2020-01-03 10:00:00.000');
        $end = new DateTime('2020-01-01 10:00:00.000');

        $speaker1 = $this->prophesize(Happening\Speaker::class);
        $speaker2 = $this->prophesize(Happening\Speaker::class);
        $speaker1->getUser()->willReturn($otherUser->reveal());
        $speaker2->getUser()->willReturn($user->reveal());

        $happening->isWebinarRecorded()->willReturn(true);
        $happening->getEnd()->willReturn($end);
        $happening->getSpeakers()->willReturn([$speaker1, $speaker2]);
        $happening->hasWebinarRecordZipFileUrl()->willReturn(false);

        $isRecordedFileAccessibleForUser = new IsRecordedFileAccessibleForUser($date);

        $this->assertFalse($isRecordedFileAccessibleForUser->isSatisfiedBy($happening->reveal(), $user->reveal()));
    }

    public function testIsSatisfiedByAllGreen(): void
    {
        $happening = $this->prophesize(Happening::class);
        $user = $this->prophesize(User::class);
        $otherUser = $this->prophesize(User::class);
        $date = new DateTime('2020-01-03 10:00:00.000');
        $end = new DateTime('2020-01-01 10:00:00.000');

        $speaker1 = $this->prophesize(Happening\Speaker::class);
        $speaker2 = $this->prophesize(Happening\Speaker::class);
        $speaker1->getUser()->willReturn($otherUser->reveal());
        $speaker2->getUser()->willReturn($user->reveal());

        $happening->isWebinarRecorded()->willReturn(true);
        $happening->getEnd()->willReturn($end);
        $happening->getSpeakers()->willReturn([$speaker1, $speaker2]);
        $happening->hasWebinarRecordZipFileUrl()->willReturn(true);

        $isRecordedFileAccessibleForUser = new IsRecordedFileAccessibleForUser($date);

        $this->assertTrue($isRecordedFileAccessibleForUser->isSatisfiedBy($happening->reveal(), $user->reveal()));
    }
}
