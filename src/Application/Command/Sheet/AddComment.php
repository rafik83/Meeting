<?php

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Sheet;

class AddComment implements Command
{
    /** @var Admin */
    public $author;

    /** @var string|string */
    public $text;

    /** @var Sheet */
    public $sheet;

    /** @var null|string */
    public $commercialStatus;

    /** @var null|\DateTimeInterface */
    public $reminderDate;

    /**
     * @param Sheet $sheet
     * @param Admin $author
     */
    public function __construct(Sheet $sheet, Admin $author)
    {
        $this->sheet = $sheet;
        $this->author = $author;
        $this->commercialStatus = $sheet->getCommercialStatus();
        $this->reminderDate = $sheet->getReminderDate();
    }

    /**
     * @return bool
     */
    public function commercialStatusChangeOrCommentNotEmptyOrReminderDateNotEmpty(): bool
    {
        if ($this->sheet->getCommercialStatus() !== $this->commercialStatus) {
            return true;
        }

        if (!empty($this->text)) {
            return true;
        }

        if ($this->sheet->getReminderDate() !== $this->reminderDate) {
            return true;
        }

        return false;
    }
}
