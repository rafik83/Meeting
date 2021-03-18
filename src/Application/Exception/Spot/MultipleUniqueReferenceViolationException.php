<?php

namespace Proximum\Vimeet\Application\Exception\Spot;

class MultipleUniqueReferenceViolationException extends SpotException
{
    /**
     * @var array
     */
    private $references;

    /**
     * UniqueReferenceViolationException constructor.
     *
     * @param array           $references
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(array $references, $code = 0, \Exception $previous = null)
    {
        $this->references = $references;
        parent::__construct(sprintf('References "%s" already exist.', $this->getReferencesAsString()), $code, $previous);
    }

    /**
     * Get references
     *
     * @return array
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * Get references as string
     *
     * @return string
     */
    public function getReferencesAsString()
    {
        return implode(', ', $this->references);
    }
}
