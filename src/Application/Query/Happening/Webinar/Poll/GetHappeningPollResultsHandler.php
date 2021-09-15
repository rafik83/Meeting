<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar\Poll;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Repository\Happening\PollVoteRepositoryInterface;

class GetHappeningPollResultsHandler implements Query
{
    private PollVoteRepositoryInterface $pollVoteRepository;

    public function __construct(PollVoteRepositoryInterface $pollVoteRepository)
    {
        $this->pollVoteRepository = $pollVoteRepository;
    }

    public function handle(GetHappeningPollResults $query): PollResultsView
    {
        $results = $this->pollVoteRepository->getResults($query->poll);

        return new PollResultsView(
            array_map(
                static fn(PollChoiceResult $pollChoiceResult) => new PollChoiceResultView(
                    $pollChoiceResult->getPollChoice()->getId(), $pollChoiceResult->getVoteCount()
                )
                ,
                $results
            )
        );
    }
}
