<?php

namespace Application\Command\Happening\Webinar\Question;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
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

    public function setUp()
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

    public function test_vote_happening_question()
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
        $question = $this->prophesize(Question::class);
        $question->getCreatedBy()->shouldBeCalled()->willReturn($questionAuthor->reveal());
        $question->getHappening()->shouldBeCalled()->willReturn($this->prophesize(Happening::class));

        $this->questionRepository->findById(42)->shouldBeCalled()->willReturn($question->reveal());
        $this->questionVoteRepository
            ->getByQuestionAndUser($question->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn(null);

        $this->questionVoteRepository->add(Argument::type(QuestionVote::class))->shouldBeCalled();

        $this->voteHappeningQuestionHandler->handle($voteHappeningQuestion->reveal());
    }

    public function test_unvote_happening_question()
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
        $question = $this->prophesize(Question::class);
        $question->getCreatedBy()->shouldBeCalled()->willReturn($questionAuthor->reveal());
        $question->getHappening()->shouldBeCalled()->willReturn($this->prophesize(Happening::class));

        $this->questionRepository->findById(42)->shouldBeCalled()->willReturn($question->reveal());

        $questionVote = $this->prophesize(QuestionVote::class);
        $this->questionVoteRepository
            ->getByQuestionAndUser($question->reveal(), $user->reveal())
            ->shouldBeCalled()
            ->willReturn($questionVote->reveal());

        $this->questionVoteRepository->remove($questionVote->reveal())->shouldBeCalled();
        $this->questionVoteRepository->add(Argument::any())->shouldNotBeCalled();

        $this->voteHappeningQuestionHandler->handle($voteHappeningQuestion->reveal());
    }

    public function test_vote_unexpected_question()
    {
        $this->expectException(QuestionNotFoundException::class);

        $voteHappeningQuestion = $this->prophesize(VoteHappeningQuestion::class);
        $voteHappeningQuestion->getQuestionId()
            ->shouldBeCalled()
            ->willReturn(42);

        $this->questionRepository->findById(42)->shouldBeCalled()->willReturn(null);

        $this->questionVoteRepository->add(Argument::any())->shouldNotBeCalled();

        $this->voteHappeningQuestionHandler->handle($voteHappeningQuestion->reveal());
    }

    public function test_vote_self_question()
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

        $this->voteHappeningQuestionHandler->handle($voteHappeningQuestion->reveal());
    }
}
