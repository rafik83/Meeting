<?php

namespace Proximum\Vimeet\Application\Command\Package\Model;

use Proximum\Vimeet\Domain\Model\Product;

class Plans
{
    /**
     * @var array
     */
    public $labels;

    /**
     * @var bool
     */
    public $enabled;

    /**
     * @var Product[]
     */
    public $plans;

    /**
     * Options constructor.
     *
     * @param array     $labels
     * @param bool      $enabled
     * @param Product[] $plans
     */
    public function __construct(array $labels, $enabled, array $plans)
    {
        foreach ($plans as $plan) {
            if (!$plan->isPlan()) {
                throw new \RuntimeException();
            }
        }

        $this->labels   = $labels;
        $this->enabled  = $enabled;
        $this->plans = $plans;
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
