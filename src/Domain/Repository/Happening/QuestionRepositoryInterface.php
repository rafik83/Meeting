<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Happening\Question;
use Proximum\Vimeet\Domain\Model\User;

interface QuestionRepositoryInterface
{
    /**
     * @param Question $question
     */
    public function add(Question $question);

    /**
     * @param User      $user
     * @param Happening $happening
     *
     * @return null|Question
     */
    public function getByUserAndHappening(User $user, Happening $happening);

    /**
     * @param User      $user
     * @param Happening $happening
     */
    public function removeQuestionFromUserForHappening(User $user, Happening $happening);
}
