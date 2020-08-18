<?php

namespace Proximum\Vimeet\Application\Command\Happening\Webinar\Record;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Happening;

class PrepareZipRecordArchive implements Command
{
    /** @var Happening */
    public $happening;

    /** @var Admin|null */
    public $admin;

    /** @var string|null */
    public $locale;

    public function __construct(
        Happening $happening,
        ?Admin $admin = null,
        ?string $locale = null
    ) {
        $this->happening = $happening;
        $this->admin = $admin;
        $this->locale = $locale;
    }
}
