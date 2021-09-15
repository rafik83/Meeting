<?php

namespace Proximum\Vimeet\Application\Command\InvoicePrefix;

use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Repository\Invoice\PrefixRepositoryInterface;

class CreateHandler
{
    /**
     * @var PrefixRepositoryInterface
     */
    private $prefixRepository;

    /**
     * CreateHandler constructor.
     *
     * @param PrefixRepositoryInterface $prefixRepository
     */
    public function __construct(PrefixRepositoryInterface $prefixRepository)
    {
        $this->prefixRepository = $prefixRepository;
    }

    /**
     * @param Create $create
     */
    public function handle(Create $create)
    {
        $this->prefixRepository->add(new Prefix($create->title, $create->prefix));
    }
}
