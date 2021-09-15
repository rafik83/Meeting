<?php

namespace Proximum\Vimeet\Domain\Model;

class PackageOptionRank
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
     * @var Product
     */
    private $option;

    /**
     * @var int
     */
    private $rank;

    /**
     * PackageOption constructor.
     *
     * @param PackageGroup $group
     * @param Product      $option
     * @param int          $rank
     */
    public function __construct(PackageGroup $group, Product $option, $rank)
    {
        if (!$option->isOption()) {
            throw new \DomainException(sprintf('Product of type "%s" expected. Type "%s" given.', Product::TYPE_OPTION, $option->getType()));
        }

        $this->group  = $group;
        $this->option = $option;
        $this->rank   = $rank;
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
     * Get option
     *
     * @return Product
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * Get rank
     *
     * @return int
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set rank
     *
     * @param int $rank
     *
     * @return PackageOptionRank
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }
}
