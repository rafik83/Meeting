<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar\Question;

use Proximum\Vimeet\Domain\Event\Day\DayHelper;
use Proximum\Vimeet\Domain\Event\GetTimezoneHelper;
use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;

class GetHappeningQuestionsHandler
{
    /** @var QuestionRepositoryInterface */
    private $questionRepository;

    /** @var GetTimezoneHelper */
    private $getTimezoneHelper;

    public function __construct(QuestionRepositoryInterface $questionRepository, GetTimezoneHelper $getTimezoneHelper)
    {
        $this->questionRepository = $questionRepository;
        $this->getTimezoneHelper = $getTimezoneHelper;
    }

    public function handle(GetHappeningQuestions $query): array
    {
        $questions = $this->questionRepository->getByHappeningDuringWebinar($query->getHappening(), $query->getUser());

        $timezone = $this->getTimezoneHelper
            ->getTimezoneByEventAndUser($query->getHappening()->getEvent(), $query->getUser());
        $mediumHourFormatter = DayHelper::getMediumHourFormatter($query->getLocale(), $timezone);

        $questionViews = [];

        foreach ($questions as [$question, $voteCount, $userVoteCount]) {
            $author = $question->getCreatedBy();

            $questionViews[] = new QuestionView(
                $question->getId(),
                $question->getContent(),
                $author->getFirstName(),
                $author->getLastName(),
                $author->getPosition(),
                $author->getAvatar(),
                $question->getSheet()->getTitle(),
                $mediumHourFormatter->format($question->getCreatedAt()),
                $voteCount,
                $userVoteCount > 0
            );
        }

        return $questionViews;
    }
}
