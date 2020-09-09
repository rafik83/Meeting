<?php

namespace Proximum\Vimeet\Domain\Repository\Happening;

use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Model\Happening\QuestionVote;
use Proximum\Vimeet\Domain\Model\User;

interface QuestionVoteRepositoryInterface
{
    public function add(QuestionVote $questionVote);

    public function remove(QuestionVote $questionVote);

    public function getByQuestionAndUser(Question $question, User $user): ?QuestionVote;
}
