<?php


namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Question;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Question\ReplyHappeningQuestion;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Question\ReplyHappeningQuestionHandler;
use Proximum\Vimeet\Application\Exception\Happening\ReplyQuestionNotAllowedException;
use Proximum\Vimeet\Domain\Exception\Happening\Webinar\HappeningQuestionNotFound;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;

class ReplyHappeningQuestionHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $questionRepository;

    /** @var ObjectProphecy */
    private $notificationPublisher;

    /** @var ReplyHappeningQuestionHandler */
    private $replyHappeningQuestion;

    /** @var \DateTime */
    private $dateTime;

    protected function setUp()
    {
        $this->dateTime = \DateTime::createFromFormat('!Y-m-d H:i', '2020-08-02 12:00');
        $this->questionRepository = $this->prophesize(QuestionRepositoryInterface::class);
        $this->notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);

        $this->replyHappeningQuestion = new ReplyHappeningQuestionHandler(
            $this->questionRepository->reveal(),
            $this->notificationPublisher->reveal(),
            $this->dateTime
        );
    }

    public function test_reply_happening_question(): void
    {
        $replyContent = '42';

        $question = $this->prophesize(Question::class);
        $happening = $this->prophesize(Happening::class);
        $question->getHappening()->willReturn($happening->reveal());

        $this->questionRepository->findById(123)->shouldBeCalled()->willReturn($question->reveal());

        $repliedBy = $this->prophesize(User::class);
        $happening->hasSpeaker($repliedBy->reveal())->shouldBeCalled()->willReturn(true);

        $question->getRepliedBy()->willReturn(null);

        $question->setReply($replyContent, $repliedBy->reveal(), $this->dateTime)->shouldBeCalled();

        $this->questionRepository->update($question->reveal())->shouldBeCalled();

        $this->questionRepository->getMessagesCountDuringHappening($happening->reveal())->willReturn(12);
        $this->notificationPublisher
            ->publishHappeningNotification($happening->reveal(), 'questions', ['action' => 'update', 'msg_count' => 12])
            ->shouldBeCalled();

        $this->replyHappeningQuestion->handle(new ReplyHappeningQuestion(
            123,
            $repliedBy->reveal(),
            $replyContent
        ));
    }

    public function test_no_content(): void
    {
        $repliedBy = $this->prophesize(User::class);

        $this->questionRepository->update(Argument::any())->shouldNotBeCalled();

        $this->notificationPublisher->publishHappeningNotification(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $this->replyHappeningQuestion->handle(new ReplyHappeningQuestion(
            123,
            $repliedBy->reveal(),
            '  '
        ));
    }

    public function test_question_not_found(): void
    {
        $this->expectException(HappeningQuestionNotFound::class);

        $repliedBy = $this->prophesize(User::class);

        $this->questionRepository->findById(123)->shouldBeCalled()->willReturn(null);

        $this->questionRepository->update(Argument::any())->shouldNotBeCalled();

        $this->notificationPublisher->publishHappeningNotification(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $this->replyHappeningQuestion->handle(new ReplyHappeningQuestion(
            123,
            $repliedBy->reveal(),
            'To be or not to be?'
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

        $this->notificationPublisher->publishHappeningNotification(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $this->replyHappeningQuestion->handle(new ReplyHappeningQuestion(
            123,
            $repliedBy->reveal(),
            'To be or not to be?'
        ));
    }

    public function test_question_user_is_speaker_but_not_reply_author(): void
    {
        $this->expectException(ReplyQuestionNotAllowedException::class);

        $repliedBy = $this->prophesize(User::class);

        $question = $this->prophesize(Question::class);
        $happening = $this->prophesize(Happening::class);
        $question->getHappening()->willReturn($happening->reveal());

        $this->questionRepository->findById(123)->shouldBeCalled()->willReturn($question->reveal());

        $happening->hasSpeaker($repliedBy->reveal())->shouldBeCalled()->willReturn(true);
        $replyRealAuthor = $this->prophesize(User::class);
        $replyRealAuthor->getId()->willReturn(2);
        $repliedBy->getId()->willReturn(1);
        $question->getRepliedBy()->willReturn($replyRealAuthor->reveal());

        $this->questionRepository->update(Argument::any())->shouldNotBeCalled();

        $this->notificationPublisher->publishHappeningNotification(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $this->replyHappeningQuestion->handle(new ReplyHappeningQuestion(
            123,
            $repliedBy->reveal(),
            'To be or not to be?'
        ));
    }
}
