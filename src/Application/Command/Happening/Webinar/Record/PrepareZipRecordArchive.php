<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Happening;

class PrepareZipRecordArchive implements Command
{
    /** @var Happening */
    public $happening;

    /** @var Admin */
    public $admin;

    /** @var string */
    public $locale;

    public function __construct(
        Happening $happening,
        Admin $admin,
        string $locale
    ) {
        $this->happening = $happening;
        $this->admin = $admin;
        $this->locale = $locale;
    }
}
