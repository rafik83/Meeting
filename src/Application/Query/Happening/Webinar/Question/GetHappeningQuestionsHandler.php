<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar\Question;

use Proximum\Vimeet\Domain\Repository\Happening\QuestionRepositoryInterface;

class GetHappeningQuestionsHandler
{
    /** @var QuestionRepositoryInterface */
    private $questionRepository;

    public function __construct(QuestionRepositoryInterface $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    public function handle(GetHappeningQuestions $query): array
    {
        $questions = $this->questionRepository->getByHappeningDuringWebinar($query->getHappening());
        $questionViews = [];

        foreach ($questions as $question) {
            $author = $question->getCreatedBy();

            $questionViews[] = new QuestionView(
                $question->getContent(),
                $author->getFirstName(),
                $author->getLastName(),
                $author->getPosition(),
                $author->getAvatar(),
                $question->getSheet()->getTitle(),
                $question->getCreatedAt()
            );
        }

        return $questionViews;
    }
}
