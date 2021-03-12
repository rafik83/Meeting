<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Happening;

use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;

class EndHappeningRedirect
{
    public Sheet $sheet;

    public Happening $happening;

    public function __construct(
        Sheet $sheet,
        Happening $happening
    ) {
        $this->sheet = $sheet;
        $this->happening = $happening;
    }
}
