<?php


namespace Proximum\Vimeet\Tests\Ui\Bundle\EventBundle\Handler\Happening;


use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\Happening\CanEvaluateHappening;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\Meeting;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;
use Proximum\Vimeet\Domain\Time\TimeRangeInterface;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Happening\PreviousHappeningEvaluationChecker;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Happening\PreviousHappeningEvaluationCheckerHandler;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\RouterInterface;

class PreviousHappeningEvaluationCheckerHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $happeningParticipationRepository, $canEvaluateHappening, $router, $flashBag, $event, $sheet, $user, $happening, $timeRange;

    public function setUp(): void
    {
        $this->happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $this->canEvaluateHappening = $this->prophesize(CanEvaluateHappening::class);
        $this->flashBag = $this->prophesize(FlashBagInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);
        $this->event = $this->prophesize(Event::class);
        $this->sheet = $this->prophesize(Sheet::class);
        $this->user = $this->prophesize(User::class);
        $this->happening = $this->prophesize(Happening::class);
        $this->happening->getEvent()->willReturn($this->event->reveal());
        $this->timeRange = $this->prophesize(TimeRangeInterface::class);
    }

    public function testHandleNotMandatoryToEvaluate(): void
    {

        $begin = new \DateTime();
        $this->timeRange->getBegin()->shouldBeCalled()->willReturn($begin);
        $this->happeningParticipationRepository->getPreviousMandatoryEvaluation(
            $this->event->reveal(),
            $this->user->reveal(),
            $begin
        )->shouldBeCalled()->willReturn(null);

        $handler = new PreviousHappeningEvaluationCheckerHandler(
            $this->happeningParticipationRepository->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->canEvaluateHappening->reveal()
        );

        $command = new PreviousHappeningEvaluationChecker(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            $this->timeRange->reveal(),
            'origin',
        );
        $handler($command);
    }

    public function testHandleNoPreviousHappening(): void
    {
        $begin = new \DateTime();
        $this->timeRange->getBegin()->shouldBeCalled()->willReturn($begin);

        $this->happeningParticipationRepository
            ->getPreviousMandatoryEvaluation(
                $this->event->reveal(),
                $this->user->reveal(),
                $begin
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $this->flashBag
            ->add('warning', 'flash.meeting.evaluation.previous_happening_not_evaluate.warning')
            ->shouldNotBeCalled()
        ;

        $this->router
            ->generate('event_happening_evaluation', Argument::any())
            ->shouldNotBeCalled()
        ;

        $handler = new PreviousHappeningEvaluationCheckerHandler(
            $this->happeningParticipationRepository->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->canEvaluateHappening->reveal()
        );

        $command = new PreviousHappeningEvaluationChecker(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            $this->timeRange->reveal(),
            'origin',
        );
        $result = $handler($command);

        $this->assertNull($result);
    }

    public function testHandleNotIsSatisfiableBy(): void
    {
        $begin = new \DateTime();
        $this->timeRange->getBegin()->shouldBeCalled()->willReturn($begin);
        $happeningParticipation = $this->prophesize(HappeningParticipation::class);

        $this->happeningParticipationRepository
            ->getPreviousMandatoryEvaluation(
                $this->event->reveal(),
                $this->user->reveal(),
                $begin
            )
            ->shouldBeCalled()
            ->willReturn($happeningParticipation->reveal())
        ;
        $happeningParticipation->getHappening()->shouldBeCalled()->willReturn($this->happening->reveal());
        $this->canEvaluateHappening->isSatisfiableBy($this->happening->reveal(), $this->user->reveal())->shouldBeCalled()->willReturn(false);

        $this->flashBag
            ->add('warning', 'flash.meeting.evaluation.previous_happening_not_evaluate.warning')
            ->shouldNotBeCalled()
        ;

        $this->router
            ->generate('event_happening_evaluation', Argument::any())
            ->shouldNotBeCalled()
        ;

        $handler = new PreviousHappeningEvaluationCheckerHandler(
            $this->happeningParticipationRepository->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->canEvaluateHappening->reveal()
        );

        $command = new PreviousHappeningEvaluationChecker(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            $this->timeRange->reveal(),
            'origin',
        );
        $result = $handler($command);

        $this->assertNull($result);
    }

    public function testHandleGetEvaluationNotNull(): void
    {
        $begin = new \DateTime();
        $this->timeRange->getBegin()->shouldBeCalled()->willReturn($begin);
        $happeningParticipation = $this->prophesize(HappeningParticipation::class);

        $this->happeningParticipationRepository
            ->getPreviousMandatoryEvaluation(
                $this->event->reveal(),
                $this->user->reveal(),
                $begin
            )
            ->shouldBeCalled()
            ->willReturn($happeningParticipation->reveal())
        ;
        $happeningParticipation->getHappening()->shouldBeCalled()->willReturn($this->happening->reveal());
        $this->canEvaluateHappening->isSatisfiableBy($this->happening->reveal(), $this->user->reveal())->shouldBeCalled()->willReturn(true);

        $this->flashBag
            ->add('warning', 'flash.meeting.evaluation.previous_happening_not_evaluate.warning')
            ->shouldNotBeCalled()
        ;

        $this->router
            ->generate('event_happening_evaluation', Argument::any())
            ->shouldNotBeCalled()
        ;

        $happeningParticipation->getEvaluation()->shouldBeCalled()->willReturn(4);


        $handler = new PreviousHappeningEvaluationCheckerHandler(
            $this->happeningParticipationRepository->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->canEvaluateHappening->reveal()
        );

        $command = new PreviousHappeningEvaluationChecker(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            $this->timeRange->reveal(),
            'origin',
        );
        $result = $handler($command);

        $this->assertNull($result);
    }


    public function testHandle(): void
    {
        $begin = new \DateTime();
        $this->timeRange->getBegin()->shouldBeCalled()->willReturn($begin);
        $happeningParticipation = $this->prophesize(HappeningParticipation::class);

        $this->happeningParticipationRepository
            ->getPreviousMandatoryEvaluation(
                $this->event->reveal(),
                $this->user->reveal(),
                $begin
            )
            ->shouldBeCalled()
            ->willReturn($happeningParticipation->reveal())
        ;
        $happeningParticipation->getHappening()->shouldBeCalled()->willReturn($this->happening->reveal());
        $this->canEvaluateHappening->isSatisfiableBy($this->happening->reveal(), $this->user->reveal())->shouldBeCalled()->willReturn(true);

        $this->flashBag
            ->add('warning', 'flash.meeting.evaluation.previous_happening_not_evaluate.warning')
            ->shouldBeCalled()
        ;

        $this->sheet->getId()->shouldBeCalled()->willReturn(12);
        $this->happening->getId()->shouldBeCalled()->willReturn(11);

        $happeningParticipation->getEvaluation()->shouldBeCalled()->willReturn(null);
        $this->router
            ->generate(
                'event_happening_evaluation', [
                    'sheet' => 12,
                    'happening' =>11,
                    'redirectTo' => 'origin'
                ]
            )->shouldBeCalled()
            ->willReturn('/route/to/evaluate')
        ;

        $handler = new PreviousHappeningEvaluationCheckerHandler(
            $this->happeningParticipationRepository->reveal(),
            $this->router->reveal(),
            $this->flashBag->reveal(),
            $this->canEvaluateHappening->reveal()
        );

        $command = new PreviousHappeningEvaluationChecker(
            $this->event->reveal(),
            $this->sheet->reveal(),
            $this->user->reveal(),
            $this->timeRange->reveal(),
            'origin',
        );
        $result = $handler($command);

        $this->assertInstanceOf(
            RedirectResponse::class,
            $result
        );
        $this->assertEquals(
            '/route/to/evaluate',
            $result->getTargetUrl()
        );
    }
}
