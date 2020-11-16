<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Question;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Question\VoteHappeningQuestion;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Question\VoteHappeningQuestionHandler;
use Proximum\Vimeet\Application\Exception\Happening\QuestionNotAllowedException;
use Proximum\Vimeet\Application\Exception\Happening\QuestionNotFoundException;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Model\Happening\QuestionVote;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionVoteRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\Mercure\AbstractNotification;

class VoteHappeningQuestionHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $questionRepository;

    /** @var ObjectProphecy */
    private $questionVoteRepository;

    /** @var ObjectProphecy */
    private $notificationPublisher;

    /** @var VoteHappeningQuestionHandler */
    private $voteHappeningQuestionHandler;

    public function setUp(): void
    {
        $this->questionRepository = $this->prophesize(QuestionRepositoryInterface::class);
        $this->questionVoteRepository = $this->prophesize(QuestionVoteRepositoryInterface::class);
        $this->notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);

        $this->voteHappeningQuestionHandler = new VoteHappeningQuestionHandler(
            $this->questionRepository->reveal(),
            $this->questionVoteRepository->reveal(),
            $this->notificationPublisher->reveal()
        );
    }

    public function test_vote_happening_question(): void
    {
        $user = $this->prophesize(User::class);
        $user->getId()->shouldBeCalled()->willReturn(1234);

        $voteHappeningQuestion = $this->prophesize(VoteHappeningQuestion::class);
        $voteHappeningQuestion->getQuestionId()
            ->shouldBeCalled()
            ->willReturn(42);
        $voteHappeningQuestion->getUser()
            ->shouldBeCalled()
            ->willReturn($user->reveal());

        $questionAuthor = $this->prophesize(User::class);
        $questionAuthor->getId()->shouldBeCalled()->willReturn(24);
        $happening = $this->prophesize(Happening::class);
        $question = $this->prophesize(Question::class);
        $question->getCreatedBy()->shouldBeCalled()->willReturn($questionAuthor->reveal());
        $question->getHappening()->shouldBeCalled()->willReturn($happening->reveal());

        $this->questionRepository->findById(42)->shouldBeCalled()->willReturn($question->reveal());
        $this->questionRepository
            ->getMessagesCountDuringHappening($happening->reveal())->willReturn(2000);
        $this->questionVoteRepository
            ->getByQuestionAndUser($question->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $this->notificationPublisher
            ->publishHappeningNotification($happening->reveal(), AbstractNotification::TYPE_QUESTIONS, [
            'action' => 'update',
            'msg_count' => 2000,
        ])->shouldBeCalled();

        $this->questionVoteRepository->add(Argument::type(QuestionVote::class))->shouldBeCalled();

        $this->voteHappeningQuestionHandler->handle($voteHappeningQuestion->reveal());
    }

    public function test_unvote_happening_question(): void
    {
        $user = $this->prophesize(User::class);
        $user->getId()->shouldBeCalled()->willReturn(1234);

        $voteHappeningQuestion = $this->prophesize(VoteHappeningQuestion::class);
        $voteHappeningQuestion->getQuestionId()
            ->shouldBeCalled()
            ->willReturn(42);
        $voteHappeningQuestion->getUser()
            ->shouldBeCalled()
            ->willReturn($user->reveal());

        $questionAuthor = $this->prophesize(User::class);
        $questionAuthor->getId()->shouldBeCalled()->willReturn(24);
        $happening = $this->prophesize(Happening::class);
        $question = $this->prophesize(Question::class);
        $question->getCreatedBy()->shouldBeCalled()->willReturn($questionAuthor->reveal());
        $question->getHappening()->shouldBeCalled()->willReturn($happening);

        $this->questionRepository->findById(42)->shouldBeCalled()->willReturn($question->reveal());
        $this->questionRepository
            ->getMessagesCountDuringHappening($happening->reveal())->willReturn(2000);

        $questionVote = $this->prophesize(QuestionVote::class);
        $this->questionVoteRepository
            ->getByQuestionAndUser($question->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn($questionVote->reveal());

        $this->questionVoteRepository->remove($questionVote->reveal())->shouldBeCalled();
        $this->questionVoteRepository->add(Argument::any())->shouldNotBeCalled();

        $this->notificationPublisher
            ->publishHappeningNotification($happening->reveal(), AbstractNotification::TYPE_QUESTIONS, [
                'action' => 'update',
                'msg_count' => 2000,
            ])->shouldBeCalled();

        $this->voteHappeningQuestionHandler->handle($voteHappeningQuestion->reveal());
    }

    public function test_vote_unexpected_question(): void
    {
        $this->expectException(QuestionNotFoundException::class);

        $voteHappeningQuestion = $this->prophesize(VoteHappeningQuestion::class);
        $voteHappeningQuestion->getQuestionId()
            ->shouldBeCalled()
            ->willReturn(42);

        $this->questionRepository->findById(42)->shouldBeCalled()->willReturn(null);

        $this->questionVoteRepository->add(Argument::any())->shouldNotBeCalled();

        $this->notificationPublisher->publishHappeningNotification(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $this->voteHappeningQuestionHandler->handle($voteHappeningQuestion->reveal());
    }

    public function test_vote_self_question(): void
    {
        $this->expectException(QuestionNotAllowedException::class);

        $voteHappeningQuestion = $this->prophesize(VoteHappeningQuestion::class);
        $voteHappeningQuestion->getQuestionId()
            ->shouldBeCalled()
            ->willReturn(42);
        $user = $this->prophesize(User::class);
        $user->getId()->shouldBeCalled()->willReturn(24);
        $voteHappeningQuestion->getUser()
            ->shouldBeCalled()
            ->willReturn($user->reveal());

        $questionAuthor = $this->prophesize(User::class);
        $questionAuthor->getId()->shouldBeCalled()->willReturn(24);
        $question = $this->prophesize(Question::class);
        $question->getCreatedBy()->shouldBeCalled()->willReturn($questionAuthor->reveal());

        $this->questionRepository->findById(42)->shouldBeCalled()->willReturn($question->reveal());

        $this->questionVoteRepository->add(Argument::any())->shouldNotBeCalled();

        $this->notificationPublisher->publishHappeningNotification(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $this->voteHappeningQuestionHandler->handle($voteHappeningQuestion->reveal());
    }
}
