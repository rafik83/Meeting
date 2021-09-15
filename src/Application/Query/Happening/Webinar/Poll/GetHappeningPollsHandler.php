<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar\Poll;

use Proximum\Vimeet\Domain\Model\Happening\Poll;
use Proximum\Vimeet\Domain\Poll\CanUserVoteOnPoll;
use Proximum\Vimeet\Domain\Repository\Happening\PollRepositoryInterface;

class GetHappeningPollsHandler
{
    private PollRepositoryInterface $pollRepository;
    private CanUserVoteOnPoll $canUserVoteOnPoll;
    private GetHappeningPollHandler $getHappeningPollHandler;

    public function __construct(
        PollRepositoryInterface $pollRepository,
        CanUserVoteOnPoll $canUserVoteOnPoll,
        GetHappeningPollHandler $getHappeningPollHandler
    ) {
        $this->pollRepository = $pollRepository;
        $this->canUserVoteOnPoll = $canUserVoteOnPoll;
        $this->getHappeningPollHandler = $getHappeningPollHandler;
    }

    public function handle(GetHappeningPolls $query): array
    {
        $polls = $this->pollRepository->findByHappening($query->happening, $query->status);
        $isSpeaker = $query->happening->hasSpeaker($query->user);

        return array_map(
            function (Poll $poll) use ($isSpeaker, $query) {
                $addResults = $isSpeaker || !$this->canUserVoteOnPoll->isSatisfiedBy($poll, $query->user);

                return $this->getHappeningPollHandler->handle(new GetHappeningPoll($poll, $addResults));
            },
            $polls
        );
    }
}
