<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

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

    /**
     * @param Sheet $sheet
     * @param Admin $author
     */
    public function __construct(Sheet $sheet, Admin $author)
    {
        $this->sheet = $sheet;
        $this->author = $author;
        $this->commercialStatus = $sheet->getCommercialStatus();
    }

    /**
     * @return bool
     */
    public function commercialStatusChangeOrCommentNotEmpty(): bool
    {
        if ($this->sheet->getCommercialStatus() !== $this->commercialStatus) {
            return true;
        }

        if ($this->text !== null && $this->text !== '') {
            return true;
        }

        return false;
    }
}
