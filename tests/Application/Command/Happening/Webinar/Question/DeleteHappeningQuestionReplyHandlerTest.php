<?php


namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Question;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Question\DeleteHappeningQuestionReply;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Question\DeleteHappeningQuestionReplyHandler;
use Proximum\Vimeet\Application\Exception\Happening\DeleteQuestionReplyNotAllowedException;
use Proximum\Vimeet\Domain\Exception\Happening\Webinar\HappeningQuestionNotFound;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;

class DeleteHappeningQuestionReplyHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $questionRepository;

    /** @var ObjectProphecy */
    private $notificationPublisher;

    /** @var DeleteHappeningQuestionReplyHandler */
    private $deleteHappeningQuestionReply;

    protected function setUp()
    {
        $this->questionRepository = $this->prophesize(QuestionRepositoryInterface::class);
        $this->notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);

        $this->deleteHappeningQuestionReply = new DeleteHappeningQuestionReplyHandler(
            $this->questionRepository->reveal(),
            $this->notificationPublisher->reveal()
        );
    }

    public function test_delete_happening_question_reply(): void
    {
        $deletedBy = $this->prophesize(User::class);

        $question = $this->prophesize(Question::class);
        $happening = $this->prophesize(Happening::class);
        $question->getHappening()->willReturn($happening->reveal());

        $this->questionRepository->findById(123)->shouldBeCalled()->willReturn($question->reveal());

        $happening->hasSpeaker($deletedBy->reveal())->shouldBeCalled()->willReturn(true);

        $question->deleteReply()->shouldBeCalled();

        $this->questionRepository->update($question->reveal())->shouldBeCalled();

        $this->notificationPublisher
            ->publishHappeningNotification($happening->reveal(), 'questions', ['action' => 'delete'])
            ->shouldBeCalled();

        $this->deleteHappeningQuestionReply->handle(new DeleteHappeningQuestionReply(
            123,
            $deletedBy->reveal()
        ));
    }

    public function test_question_not_found(): void
    {
        $this->expectException(HappeningQuestionNotFound::class);

        $deletedBy = $this->prophesize(User::class);

        $question = $this->prophesize(Question::class);

        $this->questionRepository->findById(123)->shouldBeCalled()->willReturn(null);

        $question->deleteReply()->shouldNotBeCalled();

        $this->questionRepository->update(Argument::any())->shouldNotBeCalled();

        $this->notificationPublisher
            ->publishHappeningNotification(Argument::cetera())
            ->shouldNotBeCalled();

        $this->deleteHappeningQuestionReply->handle(new DeleteHappeningQuestionReply(
            123,
            $deletedBy->reveal()
        ));
    }

    public function test_user_not_speaker(): void
    {
        $this->expectException(DeleteQuestionReplyNotAllowedException::class);

        $deletedBy = $this->prophesize(User::class);

        $question = $this->prophesize(Question::class);
        $happening = $this->prophesize(Happening::class);
        $question->getHappening()->willReturn($happening->reveal());

        $this->questionRepository->findById(123)->shouldBeCalled()->willReturn($question);

        $happening->hasSpeaker($deletedBy->reveal())->shouldBeCalled()->willReturn(false);

        $question->deleteReply()->shouldNotBeCalled();

        $this->questionRepository->update(Argument::any())->shouldNotBeCalled();

        $this->notificationPublisher
            ->publishHappeningNotification(Argument::cetera())
            ->shouldNotBeCalled();

        $this->deleteHappeningQuestionReply->handle(new DeleteHappeningQuestionReply(
            123,
            $deletedBy->reveal()
        ));
    }
}
