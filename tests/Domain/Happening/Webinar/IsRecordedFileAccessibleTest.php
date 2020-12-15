<?php

namespace Proximum\Vimeet\Tests\Domain\Happening\Webinar;

use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Happening\Webinar\IsRecordedFileAccessible;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Happening;

class IsRecordedFileAccessibleTest extends TestCase
{
    /** @var ObjectProphecy */
    private $happening;

    public function setUp(): void
    {
        $this->happening = $this->prophesize(Happening::class);
    }

    public function testIsSatisfiedByNotRecorded(): void
    {
        $this->happening->isWebinarRecorded()->shouldBeCalled()->willReturn(false);

        $isRecordedFileAccessible = new IsRecordedFileAccessible();
        self::assertFalse($isRecordedFileAccessible->isSatisfiedBy($this->happening->reveal()));
    }

    public function testIsSatisfiedByNoUrl(): void
    {
        $this->happening->isWebinarRecorded()->shouldBeCalled()->willReturn(true);
        $this->happening->hasWebinarRecordZipFileUrl()->shouldBeCalled()->willReturn(false);

        $isRecordedFileAccessible = new IsRecordedFileAccessible();
        self::assertFalse($isRecordedFileAccessible->isSatisfiedBy($this->happening->reveal()));
    }

    public function testIsSatisfiedByWithUrl(): void
    {
        $this->happening->isWebinarRecorded()->shouldBeCalled()->willReturn(true);
        $this->happening->hasWebinarRecordZipFileUrl()->shouldBeCalled()->willReturn(true);

        $isRecordedFileAccessible = new IsRecordedFileAccessible();
        self::assertTrue($isRecordedFileAccessible->isSatisfiedBy($this->happening->reveal()));
    }
}
