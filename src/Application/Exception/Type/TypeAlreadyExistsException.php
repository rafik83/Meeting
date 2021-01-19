<?php

namespace Proximum\Vimeet\Application\Exception\Type;

class TypeAlreadyExistsException extends \Exception
{
    /**
     * @var array
     */
    private $locales;

    /**
     * TypeAlreadyExistsException constructor.
     *
     * @param array $locales
     */
    public function __construct(array $locales)
    {
        parent::__construct();

        $this->message = sprintf('Type title for "%s" already exists.', implode(', ', $locales));
        $this->locales = $locales;
    }

    /**
     * @return array
     */
    public function getLocales()
    {
        return $this->locales;
    }
}
