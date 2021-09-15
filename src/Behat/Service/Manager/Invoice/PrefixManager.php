<?php

namespace Proximum\Vimeet\Behat\Service\Manager\Invoice;

use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Repository\Invoice\PrefixRepositoryInterface;

class PrefixManager
{
    /** @var PrefixRepositoryInterface */
    private $prefixRepository;

    /**
     * @param PrefixRepositoryInterface $prefixRepository
     */
    public function __construct(PrefixRepositoryInterface $prefixRepository)
    {
        $this->prefixRepository = $prefixRepository;
    }

    /**
     * @param string $prefixName
     * @param string $prefix
     * @param bool   $default
     *
     * @return Prefix
     */
    public function create(string $prefixName, string $prefix, $default = false): Prefix
    {
        $invoicePrefix = new Prefix(
            $prefixName,
            $prefix,
            $default
        );

        $this->prefixRepository->add($invoicePrefix);

        return $invoicePrefix;
    }
}
