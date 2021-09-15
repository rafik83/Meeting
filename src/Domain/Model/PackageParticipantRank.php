<?php

namespace Proximum\Vimeet\Domain\Model;

class PackageParticipantRank
{
    /** @var int */
    private $id;

    /** @var Package */
    private $package;

    /** @var Product */
    private $productParticipant;

    /** @var int */
    private $rank;

    /**
     * @param Package $package
     * @param Product $productParticipant
     * @param int     $rank
     */
    public function __construct(Package $package, Product $productParticipant, $rank)
    {
        if (!$productParticipant->isParticipant()) {
            throw new \DomainException('Product of type participant expected.');
        }

        $this->package = $package;
        $this->productParticipant = $productParticipant;
        $this->rank = $rank;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Package
     */
    public function getPackage(): Package
    {
        return $this->package;
    }

    /**
     * @return Product
     */
    public function getProductParticipant(): Product
    {
        return $this->productParticipant;
    }

    /**
     * @return int
     */
    public function getRank(): int
    {
        return $this->rank;
    }

    /**
     * @param int $rank
     */
    public function setRank($rank)
    {
        $this->rank = $rank;
    }
}
