<?php

namespace Proximum\Vimeet\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Exception\Messaging\SubstitutionProviderAlreadyDefinedException;
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
     * @throws UndefinedSubstitutionProviderException
     *
     * @return SubstituteInterface
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
     *
     * @throws SubstitutionProviderAlreadyDefinedException
     */
    public function registerSubstitution($tag, SubstituteInterface $substitutionProvider)
    {
        if (isset($this->substitutions[$tag])) {
            throw new SubstitutionProviderAlreadyDefinedException();
        }

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
