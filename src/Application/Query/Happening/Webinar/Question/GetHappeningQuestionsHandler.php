<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar\Question;

use Proximum\Vimeet\Domain\Event\Day\DayHelper;
use Proximum\Vimeet\Domain\Event\GetTimezoneHelper;
use Proximum\Vimeet\Domain\Model\Happening\Question;
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
        $questions = $this->questionRepository->getByHappeningDuringWebinar($query->getHappening(), $query->getUser(), $query->getOrderBy());
        $timezone = $this->getTimezoneHelper
            ->getTimezoneByEventAndUser($query->getHappening()->getEvent(), $query->getUser());
        $mediumHourFormatter = DayHelper::getMediumHourFormatter($query->getLocale(), $timezone);

        $questionViews = [];

        /** @var Question $question */
        foreach ($questions as [$question, $voteCount, $userVoteCount]) {
            $author = $question->getCreatedBy();
            $canVote = $query->getUser()->getId() !== $author->getId();

            $reply = null;
            if (null !== $question->getReplyContent()) {
                $reply = new QuestionReplyView(
                    $question->getReplyContent(),
                    $question->getRepliedBy()->getFullname(),
                    $mediumHourFormatter->format($question->getRepliedAt()),
                    $question->getRepliedBy()->getId() === $query->getUser()->getId()
                );
            }

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
                $userVoteCount > 0,
                $canVote,
                $reply
            );
        }

        return $questionViews;
    }
}
