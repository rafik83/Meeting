<?php

namespace Proximum\Vimeet\Domain\Model;

class PackageGroupTranslation
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var PackageGroup
     */
    private $group;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $label;

    /**
     * PackageGroupTranslation constructor.
     *
     * @param PackageGroup $group
     * @param string       $locale
     * @param string       $label
     */
    public function __construct(PackageGroup $group, $locale, $label)
    {
        $this->group  = $group;
        $this->locale = $locale;
        $this->label  = $label;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get group
     *
     * @return PackageGroup
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return PackageGroupTranslation
     */
    public function set($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'label' => $this->label,
        ];
    }

    /**
     * @return string
     */
    public function serializeData()
    {
        return json_encode($this->getData());
    }
}
