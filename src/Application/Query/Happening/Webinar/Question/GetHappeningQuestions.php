<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar\Question;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class GetHappeningQuestions implements Query
{
    /** @var Happening */
    private $happening;

    /** @var User */
    private $user;

    /** @var string */
    private $locale;

    public function __construct(Happening $happening, User $user, string $locale)
    {
        $this->happening = $happening;
        $this->user = $user;
        $this->locale = $locale;
    }

    public function getHappening(): Happening
    {
        return $this->happening;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }
}
