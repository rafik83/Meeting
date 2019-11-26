<?php

namespace Proximum\Vimeet\Tests\Application\Query\RegistrationPath;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Query\RegistrationPath\AnswerView;
use Proximum\Vimeet\Application\Query\RegistrationPath\EventRegistrationPathQuery;
use Proximum\Vimeet\Application\Query\RegistrationPath\EventRegistrationPathQueryHandler;
use Proximum\Vimeet\Application\Query\RegistrationPath\EventRegistrationPathView;
use Proximum\Vimeet\Application\Query\RegistrationPath\QuestionView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Answer;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Question;
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
        $expectedSecondQuestionView = new QuestionView(
            2,
            'Where do you come from?',
            [
                new AnswerView(101, 'Paris'),
                new AnswerView(102, 'London'),
                new AnswerView(103, 'New York'),
            ]
        );

        $answerView1 = new AnswerView(22, 'Yes');
        $answerView1->setNextQuestionView($expectedSecondQuestionView);

        $answerView2 = new AnswerView(23, 'No');

        $expectedQuestionView = new QuestionView(
            1,
            'Do you do meetings?',
            [
                $answerView1,
                $answerView2,
            ]
        );
        $expectedResult = new EventRegistrationPathView($expectedQuestionView);

        $firstQuestionAnswerYes = $this->prophesize(Answer::class);
        $firstQuestionAnswerYes->getId()->shouldBeCalled()->willReturn(22);
        $firstQuestionAnswerYes->getTitle('en')->shouldBeCalled()->willReturn('Yes');

        $firstQuestionAnswerNo = $this->prophesize(Answer::class);
        $firstQuestionAnswerNo->getId()->shouldBeCalled()->willReturn(23);
        $firstQuestionAnswerNo->getTitle('en')->shouldBeCalled()->willReturn('No');

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

        $secondQuestion = $this->prophesize(Question::class);
        $secondQuestion->getPreviousAnswer()->shouldBeCalled()->willReturn($firstQuestionAnswerYes->reveal());
        $secondQuestion->getId()->shouldBeCalled()->willReturn(2);
        $secondQuestion->getTitle('en')->shouldBeCalled()->willReturn('Where do you come from?');

        $secondQuestionAnswerParis = $this->prophesize(Answer::class);
        $secondQuestionAnswerParis->getId()->shouldBeCalled()->willReturn(101);
        $secondQuestionAnswerParis->getTitle('en')->shouldBeCalled()->willReturn('Paris');

        $secondQuestionAnswerLondon = $this->prophesize(Answer::class);
        $secondQuestionAnswerLondon->getId()->shouldBeCalled()->willReturn(102);
        $secondQuestionAnswerLondon->getTitle('en')->shouldBeCalled()->willReturn('London');

        $secondQuestionAnswerNewYork = $this->prophesize(Answer::class);
        $secondQuestionAnswerNewYork->getId()->shouldBeCalled()->willReturn(103);
        $secondQuestionAnswerNewYork->getTitle('en')->shouldBeCalled()->willReturn('New York');

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
            ->willReturn([$firstQuestion, $secondQuestion])
        ;

        $eventRegistrationPathQuery = new EventRegistrationPathQuery($this->event->reveal(), 'en');
        $result = $this->eventRegistrationPathQueryHandler->handle($eventRegistrationPathQuery);

        $this->assertEquals($expectedResult, $result);
    }
}
