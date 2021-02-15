<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Question;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
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

    /** @var \DateTime */
    private $dateTime;

    public function setUp(): void
    {
        $this->dateTime = new \DateTime('2020-06-02 12:00:00');
        $this->questionRepository = $this->prophesize(QuestionRepositoryInterface::class);
        $this->notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);
        $this->addHappeningQuestionHandler = new AddHappeningQuestionHandler(
            $this->questionRepository->reveal(),
            $this->notificationPublisher->reveal(),
            $this->dateTime
        );
    }

    public function test_add_happening_question(): void
    {
        $happening = $this->prophesize(Happening::class);

        $createdBy = $this->prophesize(User::class);

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
            $this->dateTime,
            'Can you develop your point about green IT?',
            true
        );

        $this->questionRepository->add($expectedQuestion)
            ->shouldBeCalled();

        $this->questionRepository->getMessagesCountDuringHappening($happening->reveal())->willReturn(2020);

        $this->notificationPublisher
            ->publishHappeningNotification($happening->reveal(), 'questions', ['action' => 'update', 'msg_count' => 2020])
            ->shouldBeCalled();

        $this->addHappeningQuestionHandler->handle($addHappeningQuestion->reveal());
    }
}
