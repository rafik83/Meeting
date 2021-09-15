<?php

namespace Proximum\Vimeet\Domain\Model\Invoice;

class Prefix
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var bool
     */
    private $isDefault = false;

    /**
     * Prefix constructor.
     *
     * @param string $title
     * @param string $prefix
     * @param bool   $isDefault
     */
    public function __construct($title, $prefix, $isDefault = false)
    {
        $this->title     = $title;
        $this->prefix    = $prefix;
        $this->isDefault = $isDefault;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->isDefault;
    }

    /**
     * @return string
     */
    public function getPrefixExample()
    {
        return sprintf('%s (%s%s-%s)', $this->title, $this->prefix, date('Y'), '0001');
    }
}
