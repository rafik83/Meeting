<?php

namespace Proximum\Vimeet\Application\Query\Happening\Webinar\Question;

use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Model\Happening;

class GetHappeningQuestions implements Query
{
    /** @var Happening */
    private $happening;

    public function __construct(Happening $happening)
    {
        $this->happening = $happening;
    }

    public function getHappening(): Happening
    {
        return $this->happening;
    }
}
