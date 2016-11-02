<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\File;

class FileExtensionGuesser
{
    /**
     * @param string $logoPath
     *
     * @return string
     */
    public function guess($logoPath)
    {
        $splInfo = new \SplFileInfo($logoPath);

        return $splInfo->getExtension();
    }
}
