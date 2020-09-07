<?php

namespace Application\Command\Happening\Webinar\Question;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Question\AddHappeningQuestion;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Question\AddHappeningQuestionHandler;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class AddHappeningQuestionHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $questionRepository;

    /** @var ObjectProphecy */
    private $notificationPublisher;

    /** @var AddHappeningQuestionHandler */
    private $addHappeningQuestionHandler;

    public function setUp()
    {
        $this->datetime = new \DateTime('2020-06-02 12:00:00');
        $this->questionRepository = $this->prophesize(QuestionRepositoryInterface::class);
        $this->notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);
        $this->addHappeningQuestionHandler = new AddHappeningQuestionHandler(
            $this->questionRepository->reveal(),
            $this->notificationPublisher->reveal(),
            $this->datetime
        );
    }

    public function test_add_happening_question()
    {
        $happening = $this->prophesize(Happening::class);

        $createdBy = $this->prophesize(User::class);
        $createdBy->getId()->shouldBeCalled()->willReturn(42);

        $sheet = SheetFactory::create(EventFactory::createEvent(), $createdBy->reveal());

        $addHappeningQuestion = $this->prophesize(AddHappeningQuestion::class);
        $addHappeningQuestion->getHappening()
            ->shouldBeCalled()
            ->willReturn($happening->reveal());
        $addHappeningQuestion->getSheet()
            ->shouldBeCalled()
            ->willReturn($sheet);
        $addHappeningQuestion->getCreatedBy()
            ->shouldBeCalled()
            ->willReturn($createdBy->reveal());
        $addHappeningQuestion->getContent()
            ->shouldBeCalled()
            ->willReturn('Can you develop your point about green IT?');

        $expectedQuestion = new Question(
            $happening->reveal(),
            $sheet,
            $createdBy->reveal(),
            $this->datetime,
            'Can you develop your point about green IT?',
            true
        );

        $this->questionRepository->add($expectedQuestion)
            ->shouldBeCalled();

        $this->notificationPublisher->publishHappeningNotification($happening->reveal(), 'questions', Argument::withEntry('action', 'update'))
            ->shouldBeCalled();

        $this->addHappeningQuestionHandler->handle($addHappeningQuestion->reveal());
    }
}
