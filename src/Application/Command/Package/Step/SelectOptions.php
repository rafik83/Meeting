<?php

namespace Proximum\Vimeet\Application\Command\Package\Step;

class SelectOptions extends AbstractStep
{
    /** @var OptionRow[] indexed by product id */
    public $options;

    /** @var string */
    public $locale;

    public function __get(int $id): OptionRow
    {
        return $this->options[$id];
    }

    public function __set(int $id, OptionRow $optionRow): void
    {
        $this->options[$id] = $optionRow;
    }
}
