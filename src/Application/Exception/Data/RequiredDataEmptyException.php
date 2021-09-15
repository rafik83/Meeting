<?php

namespace Proximum\Vimeet\Application\Exception\Data;

class RequiredDataEmptyException extends DataException
{
    /**
     * @var array
     */
    private $keys;

    /**
     * RequiredDataEmptyException constructor.
     *
     * @param array           $keys
     * @param int             $code
     * @param \Exception|null $previous
     */
    public function __construct(array $keys, $code = 0, \Exception $previous = null)
    {
        $message = count($keys) > 1 ? 'The data fields "%s" are required' : 'The data field "%s" is required';

        parent::__construct(sprintf($message, implode('", "', $keys)), $code, $previous);

        $this->keys = $keys;
    }

    /**
     * Get keys
     *
     * @return array
     */
    public function getKeys()
    {
        return $this->keys;
    }
}
