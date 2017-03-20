<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

interface TranslatableInterface
{
    /**
     * @param array $locales Event locales
     *
     * @return array
     */
    public function getTranslations(array $locales = []);
}
