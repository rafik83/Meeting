<?php


namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Handler\Evaluation;


use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;

class PreviousEvaluationCheckerHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event, $sheet, $user, $timeRange;

    public function setUp(): void
    {
        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
    }
}
