<?php


namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Question;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Question\BeginReplyHappeningQuestion;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Question\BeginReplyHappeningQuestionHandler;
use Proximum\Vimeet\Application\Exception\Happening\ReplyQuestionNotAllowedException;
use Proximum\Vimeet\Domain\Exception\Happening\Webinar\HappeningQuestionNotFound;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;

class BeginReplyHappeningQuestionHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $questionRepository;

    /** @var ObjectProphecy */
    private $notificationPublisher;

    /** @var BeginReplyHappeningQuestionHandler */
    private $beginReplyHappeningQuestion;

    protected function setUp()
    {
        $this->questionRepository = $this->prophesize(QuestionRepositoryInterface::class);
        $this->notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);

        $this->beginReplyHappeningQuestion = new BeginReplyHappeningQuestionHandler(
            $this->questionRepository->reveal(),
            $this->notificationPublisher->reveal()
        );
    }

    public function test_begin_reply_happening_question(): void
    {
        $question = $this->prophesize(Question::class);
        $happening = $this->prophesize(Happening::class);
        $question->getHappening()->willReturn($happening->reveal());

        $this->questionRepository->findById(123)->shouldBeCalled()->willReturn($question->reveal());

        $repliedBy = $this->prophesize(User::class);
        $happening->hasSpeaker($repliedBy->reveal())->shouldBeCalled()->willReturn(true);

        $repliedBy->getFullname()->willReturn('William Shakespeare');
        $repliedBy->getId()->willReturn(77);
        $this->notificationPublisher
            ->publishHappeningNotification($happening->reveal(), 'questions', [
                'action' => 'begin_reply',
                'questionId' => 123,
                'author' => 'William Shakespeare',
                'authorId' => 77,
            ])
            ->shouldBeCalled();

        $this->beginReplyHappeningQuestion->handle(new BeginReplyHappeningQuestion(
            123,
            $repliedBy->reveal()
        ));
    }

    public function test_question_not_found(): void
    {
        $this->expectException(HappeningQuestionNotFound::class);

        $repliedBy = $this->prophesize(User::class);

        $this->questionRepository->findById(123)->shouldBeCalled()->willReturn(null);

        $this->questionRepository->update(Argument::any())->shouldNotBeCalled();

        $this->notificationPublisher->publishHappeningNotification(Argument::cetera())
            ->shouldNotBeCalled();

        $this->beginReplyHappeningQuestion->handle(new BeginReplyHappeningQuestion(
            123,
            $repliedBy->reveal()
        ));
    }

    public function test_question_user_not_speaker(): void
    {
        $this->expectException(ReplyQuestionNotAllowedException::class);

        $repliedBy = $this->prophesize(User::class);

        $question = $this->prophesize(Question::class);
        $happening = $this->prophesize(Happening::class);
        $question->getHappening()->willReturn($happening->reveal());

        $this->questionRepository->findById(123)->shouldBeCalled()->willReturn($question->reveal());

        $happening->hasSpeaker($repliedBy->reveal())->shouldBeCalled()->willReturn(false);

        $this->questionRepository->update(Argument::any())->shouldNotBeCalled();

        $this->notificationPublisher->publishHappeningNotification(Argument::cetera())
            ->shouldNotBeCalled();

        $this->beginReplyHappeningQuestion->handle(new BeginReplyHappeningQuestion(
            123,
            $repliedBy->reveal()
        ));
    }
}
