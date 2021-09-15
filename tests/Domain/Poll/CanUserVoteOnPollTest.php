<?php

namespace Proximum\Vimeet\Tests\Domain\Poll;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Poll;
use Proximum\Vimeet\Domain\Model\HappeningParticipation;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Poll\CanUserVoteOnPoll;
use Proximum\Vimeet\Domain\Repository\Happening\PollVoteRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\HappeningParticipationRepositoryInterface;

class CanUserVoteOnPollTest extends TestCase
{
    public function testCantIfNotPublished(): void
    {
        // fixtures
        $poll = $this->prophesize(Poll::class);
        $user = $this->prophesize(User::class);

        $poll->getStatus()->willReturn(Poll::STATUS_DRAFT);

        // dependencies
        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $pollVoteRepository = $this->prophesize(PollVoteRepositoryInterface::class);

        // run test
        $canUserVoteOnPoll = new CanUserVoteOnPoll(
            $happeningParticipationRepository->reveal(),
            $pollVoteRepository->reveal()
        );
        $result = $canUserVoteOnPoll->isSatisfiedBy($poll->reveal(), $user->reveal());

        self::assertFalse($result);
    }

    public function testCantIfNoHappeningParticipation(): void
    {
        // fixtures
        $happening = $this->prophesize(Happening::class);

        $poll = $this->prophesize(Poll::class);
        $poll->getStatus()->willReturn(Poll::STATUS_PUBLISHED);
        $poll->getHappening()->willReturn($happening->reveal());

        $user = $this->prophesize(User::class);

        // dependencies
        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $pollVoteRepository = $this->prophesize(PollVoteRepositoryInterface::class);

        $happeningParticipationRepository->findByHappeningAndUser($happening->reveal(), $user->reveal())
            ->willReturn(null)
        ;

        // run test
        $canUserVoteOnPoll = new CanUserVoteOnPoll(
            $happeningParticipationRepository->reveal(),
            $pollVoteRepository->reveal()
        );
        $result = $canUserVoteOnPoll->isSatisfiedBy($poll->reveal(), $user->reveal());

        self::assertFalse($result);
    }

    public function testCantIfHappeningParticipationDisabled(): void
    {
        // fixtures
        $happeningParticipation = $this->prophesize(HappeningParticipation::class);
        $happeningParticipation->isDisabled()->willReturn(true);

        $happening = $this->prophesize(Happening::class);

        $poll = $this->prophesize(Poll::class);
        $poll->getStatus()->willReturn(Poll::STATUS_PUBLISHED);
        $poll->getHappening()->willReturn($happening->reveal());

        $user = $this->prophesize(User::class);

        // dependencies
        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $pollVoteRepository = $this->prophesize(PollVoteRepositoryInterface::class);

        $happeningParticipationRepository->findByHappeningAndUser($happening->reveal(), $user->reveal())
            ->willReturn($happeningParticipation->reveal())
        ;

        // run test
        $canUserVoteOnPoll = new CanUserVoteOnPoll(
            $happeningParticipationRepository->reveal(),
            $pollVoteRepository->reveal()
        );
        $result = $canUserVoteOnPoll->isSatisfiedBy($poll->reveal(), $user->reveal());

        self::assertFalse($result);
    }

    public function testCantIfSpeaker(): void
    {
        // fixtures
        $happeningParticipation = $this->prophesize(HappeningParticipation::class);
        $happeningParticipation->isDisabled()->willReturn(false);

        $user = $this->prophesize(User::class);

        $happening = $this->prophesize(Happening::class);
        $happening->hasSpeaker($user->reveal())->willReturn(true);

        $poll = $this->prophesize(Poll::class);
        $poll->getStatus()->willReturn(Poll::STATUS_PUBLISHED);
        $poll->getHappening()->willReturn($happening->reveal());

        // dependencies
        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $pollVoteRepository = $this->prophesize(PollVoteRepositoryInterface::class);

        $happeningParticipationRepository->findByHappeningAndUser($happening->reveal(), $user->reveal())
            ->willReturn($happeningParticipation->reveal())
        ;

        // run test
        $canUserVoteOnPoll = new CanUserVoteOnPoll(
            $happeningParticipationRepository->reveal(),
            $pollVoteRepository->reveal()
        );
        $result = $canUserVoteOnPoll->isSatisfiedBy($poll->reveal(), $user->reveal());

        self::assertFalse($result);
    }

    public function testCantIfAlreadyVoted(): void
    {
        // fixtures
        $happeningParticipation = $this->prophesize(HappeningParticipation::class);
        $happeningParticipation->isDisabled()->willReturn(false);

        $user = $this->prophesize(User::class);

        $happening = $this->prophesize(Happening::class);
        $happening->hasSpeaker($user->reveal())->willReturn(false);

        $poll = $this->prophesize(Poll::class);
        $poll->getStatus()->willReturn(Poll::STATUS_PUBLISHED);
        $poll->getHappening()->willReturn($happening->reveal());

        // dependencies
        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $pollVoteRepository = $this->prophesize(PollVoteRepositoryInterface::class);

        $happeningParticipationRepository->findByHappeningAndUser($happening->reveal(), $user->reveal())
            ->willReturn($happeningParticipation->reveal())
        ;

        $pollVoteRepository->hasUserVoted($poll->reveal(), $user->reveal())
            ->willReturn(true)
        ;

        // run test
        $canUserVoteOnPoll = new CanUserVoteOnPoll(
            $happeningParticipationRepository->reveal(),
            $pollVoteRepository->reveal()
        );
        $result = $canUserVoteOnPoll->isSatisfiedBy($poll->reveal(), $user->reveal());

        self::assertFalse($result);
    }

    public function testCanVote(): void
    {
        // fixtures
        $happeningParticipation = $this->prophesize(HappeningParticipation::class);
        $happeningParticipation->isDisabled()->willReturn(false);

        $user = $this->prophesize(User::class);

        $happening = $this->prophesize(Happening::class);
        $happening->hasSpeaker($user->reveal())->willReturn(false);

        $poll = $this->prophesize(Poll::class);
        $poll->getStatus()->willReturn(Poll::STATUS_PUBLISHED);
        $poll->getHappening()->willReturn($happening->reveal());

        // dependencies
        $happeningParticipationRepository = $this->prophesize(HappeningParticipationRepositoryInterface::class);
        $pollVoteRepository = $this->prophesize(PollVoteRepositoryInterface::class);

        $happeningParticipationRepository->findByHappeningAndUser($happening->reveal(), $user->reveal())
            ->willReturn($happeningParticipation->reveal())
        ;

        $pollVoteRepository->hasUserVoted($poll->reveal(), $user->reveal())
            ->willReturn(false)
        ;

        // run test
        $canUserVoteOnPoll = new CanUserVoteOnPoll(
            $happeningParticipationRepository->reveal(),
            $pollVoteRepository->reveal()
        );
        $result = $canUserVoteOnPoll->isSatisfiedBy($poll->reveal(), $user->reveal());

        self::assertTrue($result);
    }
}
