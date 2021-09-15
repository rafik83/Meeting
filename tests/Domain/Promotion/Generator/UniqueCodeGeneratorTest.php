<?php

namespace Proximum\Vimeet\Tests\Domain\Promotion\Generator;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Promotion\Checker\UniqueCodeChecker;
use Proximum\Vimeet\Domain\Promotion\Generator\CodeGeneratorInterface;
use Proximum\Vimeet\Domain\Promotion\Generator\UniqueCodeGenerator;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UniqueCodeGeneratorTest extends TestCase
{
    /** @var Event */
    private $event;

    /** @var CodeGeneratorInterface */
    private $generator;

    /** @var UniqueCodeChecker */
    private $checker;

    /** @var UniqueCodeGenerator */
    private $uniqueCodeGenerator;

    public function setUp()
    {
        $this->event               = EventFactory::createEvent();
        $this->generator           = $this->prophesize(CodeGeneratorInterface::class);
        $this->checker             = $this->prophesize(UniqueCodeChecker::class);
        $this->uniqueCodeGenerator = new UniqueCodeGenerator($this->generator->reveal(), $this->checker->reveal());
    }

    public function testGenerate()
    {
        $this->generator->generate($this->event, null)->shouldBeCalled()->willReturn('code');
        $this->checker->exists($this->event, 'code')->shouldBeCalled()->willReturn(false);

        $this->assertEquals('code', $this->uniqueCodeGenerator->generate($this->event));
    }

    public function testGenerateLoop()
    {
        $this->generator->generate($this->event, null)->shouldBeCalled()->willReturn('code');
        $this->checker->exists($this->event, 'code')->shouldBeCalled()->willReturn(true);
        $this->checker->exists($this->event, 'code2')->shouldBeCalled()->willReturn(true);
        $this->checker->exists($this->event, 'code3')->shouldBeCalled()->willReturn(false);

        $this->assertEquals('code3', $this->uniqueCodeGenerator->generate($this->event));
    }
}
