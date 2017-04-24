<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Exception\Messaging\UndefinedSubstitutionProviderException;

class SubstitutionsProviders
{
    /**
     * @var array of tag => SubstituteInterface
     */
    private $substitutions = [];

    /**
     * @param string $tag
     *
     * @return SubstituteInterface
     * @throws UndefinedSubstitutionProviderException
     */
    public function getSubstitution($tag)
    {
        if (!isset($this->substitutions[$tag])) {
            throw new UndefinedSubstitutionProviderException();
        }

        return $this->substitutions[$tag];
    }

    /**
     * @param string              $tag
     * @param SubstituteInterface $substitutionProvider
     */
    public function registerSubstitution($tag, SubstituteInterface $substitutionProvider)
    {
        $this->substitutions[$tag] = $substitutionProvider;
    }

    /**
     * @return string[]
     */
    public function getTags()
    {
        return array_keys($this->substitutions);
    }
}
