<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet\Upload;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Domain\Template\TemplateObject\MultiUploadCollectionObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\MultiUploadObject;

class MultiUploadCollection implements Command
{
    /** @var MultiUploadObject[] */
    public $multiUploadObjects;

    public function __construct(array $multiUploadObjects)
    {
        $this->multiUploadObjects = $multiUploadObjects;
    }
}
