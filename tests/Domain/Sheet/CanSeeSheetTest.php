<?php

namespace Proximum\Vimeet\Tests\Domain\Sheet;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\RuleRepositoryInterface;
use Proximum\Vimeet\Domain\Sheet\CanSeeSheet;

class CanSeeSheetTest extends TestCase
{
    /** @var RuleRepositoryInterface|ObjectProphecy */
    private $ruleRepository;

    /** @var RequestRepositoryInterface|ObjectProphecy */
    private $requestRepository;

    /** @var CanSeeSheet */
    private $canSeeSheet;

    /** @var Sheet|ObjectProphecy */
    private $seerSheet;

    /** @var Sheet|ObjectProphecy */
    private $seableSheet;

    public function setUp()
    {
        $this->ruleRepository = $this->prophesize(RuleRepositoryInterface::class);
        $this->requestRepository = $this->prophesize(RequestRepositoryInterface::class);
        $this->canSeeSheet = new CanSeeSheet($this->ruleRepository->reveal(), $this->requestRepository->reveal());
        $this->seerSheet = $this->prophesize(Sheet::class);
        $this->seableSheet = $this->prophesize(Sheet::class);
    }

    public function testByRules()
    {
        $rule = $this->prophesize(Rule::class);

        $this->ruleRepository
            ->getBySeerSheetAndSeeableSheet($this->seerSheet->reveal(), $this->seableSheet->reveal())
            ->shouldBeCalled()
            ->willReturn([$rule]);

        $this->assertTrue($this->canSeeSheet->isSatisfiedBy($this->seerSheet->reveal(), $this->seableSheet->reveal()));
    }

    public function testByMeetingRequests()
    {
        $this->ruleRepository
            ->getBySeerSheetAndSeeableSheet($this->seerSheet->reveal(), $this->seableSheet->reveal())
            ->shouldBeCalled()
            ->willReturn([]);

        $this->requestRepository
            ->hasRequestBetweenSheets($this->seerSheet->reveal(), $this->seableSheet->reveal())
            ->shouldBeCalled()
            ->willReturn(true);

        $this->assertTrue($this->canSeeSheet->isSatisfiedBy($this->seerSheet->reveal(), $this->seableSheet->reveal()));
    }

    public function testByNothing()
    {
        $this->ruleRepository
            ->getBySeerSheetAndSeeableSheet($this->seerSheet->reveal(), $this->seableSheet->reveal())
            ->shouldBeCalled()
            ->willReturn([]);

        $this->requestRepository
            ->hasRequestBetweenSheets($this->seerSheet->reveal(), $this->seableSheet->reveal())
            ->shouldBeCalled()
            ->willReturn(false);

        $this->assertFalse($this->canSeeSheet->isSatisfiedBy($this->seerSheet->reveal(), $this->seableSheet->reveal()));
    }
}
