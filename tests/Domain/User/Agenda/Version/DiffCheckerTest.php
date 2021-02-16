<?php

namespace Proximum\Vimeet\Tests\Domain\User\Agenda\Version;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffChecker;

class DiffCheckerTest extends TestCase
{
    public function testHasDiffWithDifferentVersionKey()
    {
        // Context
        $version = $this->prophesize(User\Agenda\Version::class);
        $version->getVersion()->willReturn([
            1 => ['request' => 1],
            2 => ['request' => 2],
            3 => ['request' => 3],
        ]);
        $currentVersion = [
            1 => ['request' => 1],
            2 => ['request' => 2],
        ];

        // Diff Checker
        $diffChecker = new DiffChecker();
        $result = $diffChecker->hasDiff($version->reveal(), $currentVersion);

        $this->assertTrue($result);
    }

    public function testHasDiffInSlot()
    {
        // Context
        $version = $this->prophesize(User\Agenda\Version::class);
        $version->getVersion()->willReturn([
            3 => [
                'request' => 3,
                'slot'    => 1123,
                'spot'    => 667,
            ],
        ]);
        $currentVersion = [
            3 => [
                'request' => 3,
                'slot'    => 34567890,
                'spot'    => 667,
            ],
        ];

        // Diff Checker
        $diffChecker = new DiffChecker();
        $result = $diffChecker->hasDiff($version->reveal(), $currentVersion);

        $this->assertTrue($result);
    }

    public function testHasDiffInSpot()
    {
        // Context
        $version = $this->prophesize(User\Agenda\Version::class);
        $version->getVersion()->willReturn([
            3 => [
                'request' => 3,
                'slot'    => 1123,
                'spot'    => 667,
            ],
        ]);
        $currentVersion = [
            3 => [
                'request' => 3,
                'slot'    => 1123,
                'spot'    => 4,
            ],
        ];

        // Diff Checker
        $diffChecker = new DiffChecker();
        $result = $diffChecker->hasDiff($version->reveal(), $currentVersion);

        $this->assertTrue($result);
    }

    public function testHasNoDiff()
    {
        // Context
        $version = $this->prophesize(User\Agenda\Version::class);
        $version->getVersion()->willReturn([
            1 => [
                'request' => 1,
                'slot'    => 11,
                'spot'    => 9,
            ],
            2 => [
                'request' => 2,
                'slot'    => 89,
                'spot'    => 12,
            ],
            3 => [
                'request' => 3,
                'slot'    => 1123,
                'spot'    => 667,
            ],
        ]);

        $currentVersion = [
            1 => [
                'request' => 1,
                'slot'    => 11,
                'spot'    => 9,
            ],
            2 => [
                'request' => 2,
                'slot'    => 89,
                'spot'    => 12,
            ],
            3 => [
                'request' => 3,
                'slot'    => 1123,
                'spot'    => 667,
            ],
        ];

        // Diff Checker
        $diffChecker = new DiffChecker();
        $result = $diffChecker->hasDiff($version->reveal(), $currentVersion);

        $this->assertFalse($result);
    }

    /**
     * @dataProvider checkTwoVersionProvider
     */
    public function testCheckTwoVersion($version, $index, $request, $expected)
    {
        $diffChecker = new DiffChecker();
        $result = $diffChecker->checkTwoVersion($version, $index, $request);

        $this->assertEquals($expected, $result);
    }

    public function testCheckTwoVersionInvalid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $version = ['wrong'];

        $currentVersion = [
            1 => [
                'request' => 1,
                'slot'    => 11,
                'spot'    => 9,
            ],
            2 => [
                'request' => 2,
                'slot'    => 89,
                'spot'    => 12,
            ],
            3 => [
                'request' => 3,
                'slot'    => 1123,
                'spot'    => 667,
            ],
        ];

        $diffChecker = new DiffChecker();
        $diffChecker->checkTwoVersion($version, 1, $currentVersion[1]);
    }

    public function checkTwoVersionProvider()
    {
        $version = [
            1 => [
                'request' => 1,
                'slot'    => 11,
                'spot'    => 9,
            ],
            2 => [
                'request' => 2,
                'slot'    => 89,
                'spot'    => 12,
            ],
            3 => [
                'request' => 3,
                'slot'    => 1123,
                'spot'    => 667,
            ],
        ];

        return [
            [$version, 1, ['request' => 1, 'slot' => 11, 'spot' => 10], true],
            [$version, 2, ['request' => 2, 'slot' => 90, 'spot' => 12], true],
            [$version, 3, ['request' => 3, 'slot' => 1123, 'spot' => 667], false],
        ];
    }
}
