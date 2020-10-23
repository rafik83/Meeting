<?php


namespace Proximum\Vimeet\Application\Query\Networking;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class GetSnippetQuery implements Query
{
    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    public function __construct(
        Sheet $sheet,
        User $user
    )
    {
        $this->sheet = $sheet;
        $this->user = $user;
    }
}
