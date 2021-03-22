<?php

namespace Proximum\Vimeet\Domain\Repository\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

interface QuestionRepositoryInterface
{
    /**
     * @param Question $question
     */
    public function add(Question $question): void;
    public function delete(Question $question): void;
    public function update(Question $question): void;

    /**
     * @return Question[]
     */
    public function getByUserAndHappening(User $user, Happening $happening): array;

    public function removeQuestionFromUserForHappening(User $user, Happening $happening);

    /**
     * @return Question[]
     */
    public function findByHappeningAndSheet(Happening $happening, Sheet $sheet): array;

    /**
     * @param User $currentUser to indicate which questions have been voted for
     *
     * @return Question[]
     */
    public function getByHappeningDuringWebinar(Happening $happening, User $currentUser): array;

    public function findById(int $id): ?Question;

    public function getMessagesCountDuringHappening(Happening $happening): int;
}
