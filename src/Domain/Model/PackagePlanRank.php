<?php

namespace Proximum\Vimeet\Domain\Model;

class PackagePlanRank
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Package
     */
    private $package;

    /**
     * @var Product
     */
    private $plan;

    /**
     * @var int
     */
    private $rank;

    /**
     * PackagePlanRank constructor.
     *
     * @param Package $package
     * @param Product $plan
     * @param int     $rank
     */
    public function __construct(Package $package, Product $plan, $rank)
    {
        if (!$plan->isPlan()) {
            throw new \DomainException(sprintf('Product of type "%s" expected. Type "%s" given.', Product::TYPE_PLAN, $plan->getType()));
        }

        $this->package = $package;
        $this->plan    = $plan;
        $this->rank    = $rank;
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
     * Get package
     *
     * @return Package
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Get plan
     *
     * @return Product
     */
    public function getPlan()
    {
        return $this->plan;
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
     * @return PackagePlanRank
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }
}
