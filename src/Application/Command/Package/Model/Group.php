<?php

namespace Proximum\Vimeet\Application\Command\Package\Model;

use Proximum\Vimeet\Domain\Model\Product;

class Group
{
    /**
     * @var array
     */
    public $labels;

    /**
     * @var Product[]
     */
    public $options;

    /**
     * Group constructor.
     *
     * @param array     $labels
     * @param Product[] $options
     */
    public function __construct(array $labels = [], array $options = [])
    {
        $this->labels  = $labels;
        $this->options = $options;
    }

    /**
     * @param string     $locale
     * @param mixed|null $default
     *
     * @return string|null
     */
    public function getLabel($locale, $default = null)
    {
        return isset($this->labels[$locale]) ? $this->labels[$locale] : $default;
    }
}
