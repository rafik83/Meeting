<?php


namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Question;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Question\DeleteHappeningQuestion;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Question\DeleteHappeningQuestionHandler;
use Proximum\Vimeet\Application\Exception\Happening\DeleteQuestionNotAllowedException;
use Proximum\Vimeet\Application\Exception\Happening\QuestionNotFoundException;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;
use Proximum\Vimeet\Tests\Factory\SheetFactory;

class DeleteHappeningQuestionHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $questionRepository;

    /** @var ObjectProphecy */
    private $notificationPublisher;

    /** @var DeleteHappeningQuestionHandler */
    private $deleteHappeningQuestion;

    protected function setUp()
    {
        $this->questionRepository = $this->prophesize(QuestionRepositoryInterface::class);
        $this->notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);
        $this->deleteHappeningQuestion = new DeleteHappeningQuestionHandler(
            $this->questionRepository->reveal(),
            $this->notificationPublisher->reveal()
        );
    }

    public function test_delete_happening_question(): void
    {
        $happening = $this->prophesize(Happening::class);
        $createdBy = $this->prophesize(User::class);

        $sheet = SheetFactory::create(EventFactory::createEvent(), $createdBy->reveal());

        $happening->hasSpeaker($createdBy->reveal())->shouldBeCalled()->willReturn(true);

        $expectedQuestion = new Question(
            $happening->reveal(),
            $sheet,
            $createdBy->reveal(),
            new \DateTime('2020-06-02 12:00:00'),
            'Can you develop your point about green IT?',
            true
        );

        $this->questionRepository->findById(123)->shouldBeCalled()->willReturn($expectedQuestion);

        $this->questionRepository->delete($expectedQuestion)
            ->shouldBeCalled();

        $this->notificationPublisher
            ->publishHappeningNotification($happening->reveal(), 'questions', ['action' => 'delete'])
            ->shouldBeCalled();

        $this->deleteHappeningQuestion->handle(new DeleteHappeningQuestion(
            123,
            $createdBy->reveal(),
            $happening->reveal()
        ));
    }

    public function test_hasSpeaker_Null(): void
    {
        $this->expectException(DeleteQuestionNotAllowedException::class);
        $happening = $this->prophesize(Happening::class);
        $createdBy = $this->prophesize(User::class);

        $happening->hasSpeaker($createdBy->reveal())->shouldBeCalled()->willReturn(false);
        $deleteHappeningQuestion = new DeleteHappeningQuestion(
            123,
            $createdBy->reveal(),
            $happening->reveal()
        );

        $this->questionRepository->delete(Argument::any())->shouldNotBeCalled();

        $this->notificationPublisher->publishHappeningNotification(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $this->deleteHappeningQuestion->handle($deleteHappeningQuestion);
    }

    public function test_question_Null(): void
    {
        $this->expectException(QuestionNotFoundException::class);
        $happening = $this->prophesize(Happening::class);
        $createdBy = $this->prophesize(User::class);
        $happening->hasSpeaker($createdBy->reveal())->shouldBeCalled()->willReturn(true);
        $deleteHappeningQuestion = new DeleteHappeningQuestion(
            123,
            $createdBy->reveal(),
            $happening->reveal()
        );
        $this->questionRepository->findById(123)->shouldBeCalled()->willReturn(null);

        $this->questionRepository->delete(Argument::any())->shouldNotBeCalled();

        $this->notificationPublisher->publishHappeningNotification(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $this->deleteHappeningQuestion->handle($deleteHappeningQuestion);
    }
}
