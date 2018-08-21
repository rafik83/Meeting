<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

interface QRCodeGeneratorInterface
{
    public function generateBase64Image(string $text, int $size = 300): string;
}
