<?php

namespace Proximum\Vimeet\Tests\Application\Query\Happening\Webinar\Poll;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\GetHappeningPollResults;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\GetHappeningPollResultsHandler;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\PollChoiceResult;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\PollChoiceResultView;
use Proximum\Vimeet\Application\Query\Happening\Webinar\Poll\PollResultsView;
use Proximum\Vimeet\Domain\Model\Happening\Poll;
use Proximum\Vimeet\Domain\Model\Happening\PollChoice;
use Proximum\Vimeet\Domain\Repository\Happening\PollVoteRepositoryInterface;

class GetHappeningPollResultsHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        // fixtures
        $poll = $this->prophesize(Poll::class);
        $pollChoiceYes = $this->prophesize(PollChoice::class);
        $pollChoiceYes->getId()->willReturn(314);
        $pollChoiceNo = $this->prophesize(PollChoice::class);
        $pollChoiceNo->getId()->willReturn(42);

        $pollChoiceYesResult = new PollChoiceResult($pollChoiceYes->reveal(), 30);
        $pollChoiceNoResult = new PollChoiceResult($pollChoiceNo->reveal(), 70);

        // dependencies
        $pollVoteRepository = $this->prophesize(PollVoteRepositoryInterface::class);
        $pollVoteRepository->getResults($poll->reveal())->willReturn([$pollChoiceYesResult, $pollChoiceNoResult]);

        // run
        $handler = new GetHappeningPollResultsHandler($pollVoteRepository->reveal());
        $query = new GetHappeningPollResults($poll->reveal());

        $result = $handler->handle($query);

        $expected = new PollResultsView(
            [
                new PollChoiceResultView(314, 30),
                new PollChoiceResultView(42, 70),
            ]
        );

        self::assertEquals($expected, $result);
    }
}
