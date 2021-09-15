<?php

namespace Proximum\Vimeet\Application\Command\Package\Model;

class Options
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
     * @var Group[]
     */
    public $groups;

    /**
     * Options constructor.
     *
     * @param array   $labels
     * @param bool    $enabled
     * @param Group[] $groups
     */
    public function __construct(array $labels, $enabled, array $groups)
    {
        $this->labels  = $labels;
        $this->enabled = $enabled;
        $this->groups  = $groups;
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

    /**
     * @return array
     */
    public function getGroupOptions()
    {
        return array_map(function (Group $group) { return $group->options; }, $this->groups);
    }

    /**
     * @return array
     */
    public function getGroupLabels()
    {
        return array_map(function (Group $group) { return $group->labels; }, $this->groups);
    }
}
