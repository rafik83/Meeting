<?php

namespace Proximum\Vimeet\Infrastructure\Repository\RegistrationPath;

use Doctrine\ORM\EntityManager;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\RegistrationPath\Question;
use Proximum\Vimeet\Domain\Repository\RegistrationPath\QuestionRepositoryInterface;

class QuestionRepository implements QuestionRepositoryInterface
{
    /** @var EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function add(Question $question): void
    {
        $this->entityManager->persist($question);
        $this->entityManager->flush($question);
    }

    /**
     * {@inheritdoc}
     */
    public function set(Question $question): void
    {
        $this->entityManager->flush($question);
    }

    public function remove(Question $question): void
    {
        $this->entityManager->remove($question);
        $this->entityManager->flush($question);
    }

    public function getQuestionsByEvent(Event $event, string $locale): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('question, questionTranslation, answer, answerTranslation')
            ->from(Question::class, 'question')
            ->join(
                'question.translations',
                'questionTranslation',
                'WITH',
                'question.event = :event AND questionTranslation.locale = :locale'
            )
            ->join('question.answers', 'answer')
            ->join(
                'answer.translations',
                'answerTranslation',
                'WITH',
                'answerTranslation.locale = :locale'
            )
            ->setParameter('event', $event)
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getResult()
        ;
    }
}
