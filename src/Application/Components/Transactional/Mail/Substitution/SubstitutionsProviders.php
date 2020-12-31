<?php

namespace Proximum\Vimeet\Application\Components\Transactional\Mail\Substitution;

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
    public function getSubstitution(string $tag): SubstituteInterface
    {
        if (!isset($this->substitutions[$tag])) {
            throw new UndefinedSubstitutionProviderException(
                sprintf('There is registered substitution provider for the tag "%s"', $tag)
            );
        }

        return $this->substitutions[$tag];
    }

    /**
     * @param string              $tag
     * @param SubstituteInterface $substitutionProvider
     *
     * @throws SubstitutionProviderAlreadyDefinedException
     */
    public function registerSubstitution(string $tag, SubstituteInterface $substitutionProvider)
    {
        if (isset($this->substitutions[$tag])) {
            throw new SubstitutionProviderAlreadyDefinedException(
                sprintf('The tag "%s" was already register', $tag)
            );
        }

        $this->substitutions[$tag] = $substitutionProvider;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return array_keys($this->substitutions);
    }
}
