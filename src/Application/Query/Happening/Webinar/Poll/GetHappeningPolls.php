<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar\Poll;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class GetHappeningPolls implements Query
{
    public Happening $happening;
    public User $user;
    public string $locale;
    public ?string $status;

    public function __construct(Happening $happening, User $user, string $locale, ?string $status)
    {
        $this->happening = $happening;
        $this->user = $user;
        $this->locale = $locale;
        $this->status = $status;
    }
}
