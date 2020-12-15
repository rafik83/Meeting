<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Question;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class AddHappeningQuestion implements Command
{
    /** @var Happening */
    private $happening;

    /** @var Sheet */
    private $sheet;

    /** @var User */
    private $createdBy;

    /** @var string */
    private $content;

    public function __construct(Happening $happening, Sheet $sheet, User $createdBy, string $content)
    {
        $this->happening = $happening;
        $this->sheet = $sheet;
        $this->createdBy = $createdBy;
        $this->content = $content;
    }

    public function getHappening(): Happening
    {
        return $this->happening;
    }

    public function getSheet(): Sheet
    {
        return $this->sheet;
    }

    public function getCreatedBy(): User
    {
        return $this->createdBy;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
