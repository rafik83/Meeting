<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

/**
 * Interface TranslatableInterface
 *
 * TemplateObject implement this interface in order to build TaggedDataViews with object translations data
 *
 * @see TaggedDataFactory
 */
interface TranslatableInterface
{
    /**
     * @param array $locales Event locales
     *
     * @return array
     */
    public function getTranslations(array $locales = []);
}
