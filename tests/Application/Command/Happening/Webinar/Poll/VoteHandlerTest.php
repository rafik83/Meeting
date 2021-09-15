<?php

namespace Proximum\Vimeet\Tests\Application\Command\Happening\Webinar\Poll;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\NotificationPublisherInterface;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\MultipleChoiceVoteNotAllowedException;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\NotAllowedVoteException;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\NoVoteException;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\PollChoicesNotRecognizedException;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\Vote;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\VoteHandler;
use Proximum\Vimeet\Application\Command\Happening\Webinar\Poll\VoteResultView;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\GetHappeningPoll;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\GetHappeningPollHandler;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\GetHappeningPollsHandler;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\PollView;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Poll;
use Proximum\Vimeet\Domain\Model\Happening\PollChoice;
use Proximum\Vimeet\Domain\Model\Happening\PollVote;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Poll\CanUserVoteOnPoll;
use Proximum\Vimeet\Domain\Repository\Happening\PollRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Happening\PollVoteRepositoryInterface;

class VoteHandlerTest extends TestCase
{
    public function testNotAllowedVoteExceptionHandle(): void
    {
        // fixtures
        $poll = $this->prophesize(Poll::class);
        $poll->getId()->willReturn(1);
        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(10);
        $happening = $this->prophesize(Happening::class);
        $happening->getId()->willReturn(20);
        $poll->getHappening()->willReturn($happening->reveal());

        // dependencies
        $pollVoteRepository = $this->prophesize(PollVoteRepositoryInterface::class);
        $canUserVoteOnPoll = $this->prophesize(CanUserVoteOnPoll::class);
        $notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);
        $pollRepository = $this->prophesize(PollRepositoryInterface::class);
        $getHappeningPoll = $this->prophesize(GetHappeningPollHandler::class);

        $pollRepository->findById(1)->willReturn($poll->reveal());

        $canUserVoteOnPoll->isSatisfiedBy($poll->reveal(), $user->reveal())->willReturn(false);

        // Expected exception
        $this->expectException(NotAllowedVoteException::class);

        // execute tests
        $command = new Vote(1, $happening->reveal(), $user->reveal(), [100, 101,]);

        $handler = new VoteHandler(
            $pollVoteRepository->reveal(),
            $canUserVoteOnPoll->reveal(),
            $notificationPublisher->reveal(),
            $pollRepository->reveal(),
            $getHappeningPoll->reveal()
        );
        $handler->handle($command);
    }

    public function testMultipleChoiceVoteNotAllowedException(): void
    {
        // fixtures
        $pollChoiceYes = $this->prophesize(PollChoice::class);
        $pollChoiceYes->getId()->willReturn(100);
        $pollChoiceNo = $this->prophesize(PollChoice::class);
        $pollChoiceNo->getId()->willReturn(101);

        $poll = $this->prophesize(Poll::class);
        $poll->getId()->willReturn(1);
        $poll->isMultipleChoice()->willReturn(false);
        $poll->getPollChoicesArray()->willReturn([$pollChoiceYes->reveal(), $pollChoiceNo->reveal(),]);
        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(10);
        $happening = $this->prophesize(Happening::class);
        $happening->getId()->willReturn(20);
        $poll->getHappening()->willReturn($happening->reveal());

        // dependencies
        $pollVoteRepository = $this->prophesize(PollVoteRepositoryInterface::class);
        $canUserVoteOnPoll = $this->prophesize(CanUserVoteOnPoll::class);
        $notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);
        $pollRepository = $this->prophesize(PollRepositoryInterface::class);
        $getHappeningPoll = $this->prophesize(GetHappeningPollHandler::class);

        $pollRepository->findById(1)->willReturn($poll->reveal());

        $canUserVoteOnPoll->isSatisfiedBy($poll->reveal(), $user->reveal())->willReturn(true);

        // Expected exception
        $this->expectException(MultipleChoiceVoteNotAllowedException::class);

        // execute tests
        $command = new Vote(1, $happening->reveal(), $user->reveal(), [100, 101,]);

        $handler = new VoteHandler(
            $pollVoteRepository->reveal(),
            $canUserVoteOnPoll->reveal(),
            $notificationPublisher->reveal(),
            $pollRepository->reveal(),
            $getHappeningPoll->reveal()
        );
        $handler->handle($command);
    }

    public function testPollChoicesNotRecognizedException(): void
    {
        // fixtures
        $pollChoiceYes = $this->prophesize(PollChoice::class);
        $pollChoiceYes->getId()->willReturn(100);
        $pollChoiceNo = $this->prophesize(PollChoice::class);
        $pollChoiceNo->getId()->willReturn(101);

        $poll = $this->prophesize(Poll::class);
        $poll->getId()->willReturn(1);
        $poll->isMultipleChoice()->willReturn(false);
        $poll->getPollChoicesArray()->willReturn([$pollChoiceYes->reveal(), $pollChoiceNo->reveal(),]);
        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(10);
        $happening = $this->prophesize(Happening::class);
        $happening->getId()->willReturn(20);
        $poll->getHappening()->willReturn($happening->reveal());

        // dependencies
        $pollVoteRepository = $this->prophesize(PollVoteRepositoryInterface::class);
        $canUserVoteOnPoll = $this->prophesize(CanUserVoteOnPoll::class);
        $notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);
        $pollRepository = $this->prophesize(PollRepositoryInterface::class);
        $getHappeningPoll = $this->prophesize(GetHappeningPollHandler::class);

        $pollRepository->findById(1)->willReturn($poll->reveal());

        $canUserVoteOnPoll->isSatisfiedBy($poll->reveal(), $user->reveal())->willReturn(true);

        // Expected exception
        $this->expectException(PollChoicesNotRecognizedException::class);

        // execute tests
        $command = new Vote(1, $happening->reveal(), $user->reveal(), ['foobar',]);

        $handler = new VoteHandler(
            $pollVoteRepository->reveal(),
            $canUserVoteOnPoll->reveal(),
            $notificationPublisher->reveal(),
            $pollRepository->reveal(),
            $getHappeningPoll->reveal()
        );
        $handler->handle($command);
    }

    public function testNoVoteException(): void
    {
        // fixtures
        $pollChoiceYes = $this->prophesize(PollChoice::class);
        $pollChoiceYes->getId()->willReturn(100);
        $pollChoiceNo = $this->prophesize(PollChoice::class);
        $pollChoiceNo->getId()->willReturn(101);

        $poll = $this->prophesize(Poll::class);
        $poll->getId()->willReturn(1);
        $poll->isMultipleChoice()->willReturn(false);
        $poll->getPollChoicesArray()->willReturn([$pollChoiceYes->reveal(), $pollChoiceNo->reveal(),]);
        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(10);
        $happening = $this->prophesize(Happening::class);
        $happening->getId()->willReturn(20);
        $poll->getHappening()->willReturn($happening->reveal());

        // dependencies
        $pollVoteRepository = $this->prophesize(PollVoteRepositoryInterface::class);
        $canUserVoteOnPoll = $this->prophesize(CanUserVoteOnPoll::class);
        $notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);
        $pollRepository = $this->prophesize(PollRepositoryInterface::class);
        $getHappeningPoll = $this->prophesize(GetHappeningPollHandler::class);

        $pollRepository->findById(1)->willReturn($poll->reveal());

        $canUserVoteOnPoll->isSatisfiedBy($poll->reveal(), $user->reveal())->willReturn(true);

        // Expected exception
        $this->expectException(NoVoteException::class);

        // execute tests
        $command = new Vote(1, $happening->reveal(), $user->reveal(), []);

        $handler = new VoteHandler(
            $pollVoteRepository->reveal(),
            $canUserVoteOnPoll->reveal(),
            $notificationPublisher->reveal(),
            $pollRepository->reveal(),
            $getHappeningPoll->reveal()
        );
        $handler->handle($command);
    }

    public function testHandle(): void
    {
        // fixtures
        $pollChoiceYes = $this->prophesize(PollChoice::class);
        $pollChoiceYes->getId()->willReturn(100);
        $pollChoiceNo = $this->prophesize(PollChoice::class);
        $pollChoiceNo->getId()->willReturn(101);

        $poll = $this->prophesize(Poll::class);
        $poll->getId()->willReturn(1);
        $poll->isMultipleChoice()->willReturn(false);
        $poll->getPollChoicesArray()->willReturn([$pollChoiceYes->reveal(), $pollChoiceNo->reveal(),]);
        $user = $this->prophesize(User::class);
        $user->getId()->willReturn(10);
        $happening = $this->prophesize(Happening::class);
        $happening->getId()->willReturn(20);
        $poll->getHappening()->willReturn($happening->reveal());

        $pollVote = new PollVote($pollChoiceNo->reveal(), $user->reveal());

        // dependencies
        $pollVoteRepository = $this->prophesize(PollVoteRepositoryInterface::class);
        $canUserVoteOnPoll = $this->prophesize(CanUserVoteOnPoll::class);
        $notificationPublisher = $this->prophesize(NotificationPublisherInterface::class);
        $pollRepository = $this->prophesize(PollRepositoryInterface::class);
        $getHappeningPoll = $this->prophesize(GetHappeningPollHandler::class);

        $pollRepository->findById(1)->willReturn($poll->reveal());

        $canUserVoteOnPoll->isSatisfiedBy($poll->reveal(), $user->reveal())->willReturn(true);

        $pollVoteRepository->add($pollVote)->shouldBeCalled();

        $pollView = $this->prophesize(PollView::class);

        $getHappeningPoll->handle(new GetHappeningPoll($poll->reveal(), true))->willReturn($pollView->reveal());

        $notificationPublisher->publishDelayedPollVoteNotification($poll->reveal())->shouldBeCalled();

        // execute tests
        $command = new Vote(1, $happening->reveal(), $user->reveal(), [101]);

        $handler = new VoteHandler(
            $pollVoteRepository->reveal(),
            $canUserVoteOnPoll->reveal(),
            $notificationPublisher->reveal(),
            $pollRepository->reveal(),
            $getHappeningPoll->reveal()
        );
        $viewResult = $handler->handle($command);

        $expectedView = new VoteResultView($pollView->reveal());

        self::assertEquals($expectedView, $viewResult);
    }
}
