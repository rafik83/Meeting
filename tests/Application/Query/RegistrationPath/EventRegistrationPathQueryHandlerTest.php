<?php

namespace Proximum\Vimeet\Tests\Application\Query\RegistrationPath;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\RegistrationPath\AnswerView;
use Proximum\Vimeet\Application\Query\RegistrationPath\EventRegistrationPathQuery;
use Proximum\Vimeet\Application\Query\RegistrationPath\EventRegistrationPathQueryHandler;
use Proximum\Vimeet\Application\Query\RegistrationPath\EventRegistrationPathView;
use Proximum\Vimeet\Application\Query\RegistrationPath\QuestionView;
use Proximum\Vimeet\Application\Query\RegistrationPath\TypeView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Answer;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Question;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\RegistrationPath\QuestionRepositoryInterface;

class EventRegistrationPathQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy|QuestionRepositoryInterface */
    private $questionRepository;

    /** @var EventRegistrationPathQueryHandler */
    private $eventRegistrationPathQueryHandler;

    /** @var ObjectProphecy|Event */
    private $event;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->questionRepository = $this->prophesize(QuestionRepositoryInterface::class);
        $this->eventRegistrationPathQueryHandler = new EventRegistrationPathQueryHandler(
            $this->questionRepository->reveal()
        );
    }

    public function testHandle()
    {
        $typeView1 = new TypeView(42, 'Exhibitor');
        $typeView2 = new TypeView(1242, 'Visitor');

        $expectedSecondQuestionView = new QuestionView(
            2,
            'Where do you come from?',
            []
        );
        $expectedSecondQuestionView->answerViews = [
            new AnswerView($expectedSecondQuestionView, 101, 'Paris', []),
            new AnswerView($expectedSecondQuestionView, 102, 'London', []),
            new AnswerView($expectedSecondQuestionView, 103, 'New York', [$typeView1, $typeView2]),
        ];

        $expectedQuestionView = new QuestionView(
            1,
            'Do you do meetings?',
            []
        );

        $answerView1 = new AnswerView($expectedQuestionView, 22, 'Yes', []);
        $answerView1->setNextQuestionView($expectedSecondQuestionView);
        $expectedSecondQuestionView->setPreviousAnswerView($answerView1);

        $answerView2 = new AnswerView($expectedQuestionView, 23, 'No', []);

        $expectedQuestionView->answerViews = [
            $answerView1,
            $answerView2,
        ];

        $expectedResult = new EventRegistrationPathView($expectedQuestionView);

        $firstQuestionAnswerYes = $this->prophesize(Answer::class);
        $firstQuestionAnswerYes->getId()->shouldBeCalled()->willReturn(22);
        $firstQuestionAnswerYes->getTitle('en')->shouldBeCalled()->willReturn('Yes');
        $firstQuestionAnswerYes->getTypes()->shouldBeCalled()->willReturn([]);

        $firstQuestionAnswerNo = $this->prophesize(Answer::class);
        $firstQuestionAnswerNo->getId()->shouldBeCalled()->willReturn(23);
        $firstQuestionAnswerNo->getTitle('en')->shouldBeCalled()->willReturn('No');
        $firstQuestionAnswerNo->getTypes()->shouldBeCalled()->willReturn([]);

        $firstQuestion = $this->prophesize(Question::class);
        $firstQuestion->getId()->shouldBeCalled()->willReturn(1);
        $firstQuestion->getTitle('en')->shouldBeCalled()->willReturn('Do you do meetings?');
        $firstQuestion->getPreviousAnswer()->shouldBeCalled()->willReturn(null);
        $firstQuestion
            ->getAnswers()
            ->shouldBeCalled()
            ->willReturn(
                [
                    $firstQuestionAnswerYes->reveal(),
                    $firstQuestionAnswerNo->reveal(),
                ]
            );

        $type1 = $this->prophesize(Type::class);
        $type1->getId()->shouldBeCalled()->willReturn(42);
        $type1->getTitle('en')->shouldBeCalled()->willReturn('Exhibitor');

        $type2 = $this->prophesize(Type::class);
        $type2->getId()->shouldBeCalled()->willReturn(1242);
        $type2->getTitle('en')->shouldBeCalled()->willReturn('Visitor');

        $secondQuestion = $this->prophesize(Question::class);
        $secondQuestion->getPreviousAnswer()->shouldBeCalled()->willReturn($firstQuestionAnswerYes->reveal());
        $secondQuestion->getId()->shouldBeCalled()->willReturn(2);
        $secondQuestion->getTitle('en')->shouldBeCalled()->willReturn('Where do you come from?');

        $secondQuestionAnswerParis = $this->prophesize(Answer::class);
        $secondQuestionAnswerParis->getId()->shouldBeCalled()->willReturn(101);
        $secondQuestionAnswerParis->getTitle('en')->shouldBeCalled()->willReturn('Paris');
        $secondQuestionAnswerParis->getTypes()->shouldBeCalled()->willReturn([]);

        $secondQuestionAnswerLondon = $this->prophesize(Answer::class);
        $secondQuestionAnswerLondon->getId()->shouldBeCalled()->willReturn(102);
        $secondQuestionAnswerLondon->getTitle('en')->shouldBeCalled()->willReturn('London');
        $secondQuestionAnswerLondon->getTypes()->shouldBeCalled()->willReturn([]);

        $secondQuestionAnswerNewYork = $this->prophesize(Answer::class);
        $secondQuestionAnswerNewYork->getId()->shouldBeCalled()->willReturn(103);
        $secondQuestionAnswerNewYork->getTitle('en')->shouldBeCalled()->willReturn('New York');
        $secondQuestionAnswerNewYork->getTypes()->shouldBeCalled()->willReturn([$type1->reveal(), $type2->reveal()]);

        $secondQuestion
            ->getAnswers()
            ->shouldBeCalled()
            ->willReturn(
                [
                    $secondQuestionAnswerParis->reveal(),
                    $secondQuestionAnswerLondon->reveal(),
                    $secondQuestionAnswerNewYork->reveal(),
                ]
            );

        $this->questionRepository
            ->getQuestionsByEvent($this->event->reveal(), 'en')
            ->shouldBeCalled()
            ->willReturn([$firstQuestion, $secondQuestion]);

        $eventRegistrationPathQuery = new EventRegistrationPathQuery($this->event->reveal(), 'en');
        $result = $this->eventRegistrationPathQueryHandler->handle($eventRegistrationPathQuery);

        $this->assertEquals($expectedResult, $result);
    }
}
