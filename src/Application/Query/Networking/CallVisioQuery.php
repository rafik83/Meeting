<?php


namespace Proximum\Vimeet\Application\Query\Networking;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class CallVisioQuery implements Query
{
    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $toUser;

    /** @var User */
    public $fromUser;

    /** @var string */
    public $locale;

    public function __construct(
        Sheet $sheet,
        User $fromUser,
        User $toUser,
        string $locale
    ) {
        $this->sheet = $sheet;
        $this->toUser = $toUser;
        $this->fromUser = $fromUser;
        $this->locale = $locale;
    }
}
