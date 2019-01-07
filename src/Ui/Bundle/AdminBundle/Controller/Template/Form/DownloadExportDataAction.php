<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Template\Form;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\File;
use Symfony\Component\HttpFoundation\JsonResponse;

class DownloadExportDataAction
{
    public function __construct()
    {
    }

    public function __invoke(Event $event, File $file, string $hash)
    {
        return new JsonResponse();
    }
}
