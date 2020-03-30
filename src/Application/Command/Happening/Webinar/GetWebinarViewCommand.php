<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\User;

class GetWebinarViewCommand implements Command
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
