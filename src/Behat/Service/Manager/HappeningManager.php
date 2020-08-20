<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;

class HappeningManager
{
    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    public function __construct(
        HappeningRepositoryInterface $happeningRepository
    ) {
        $this->happeningRepository = $happeningRepository;
    }

    public function createHappening(Happening\Category $category): Happening
    {
        $happening = new Happening(
            $category->getEvent(),
            new \DateTime('2020-09-01 10:00:00'),
            new \DateTime('2020-09-01 11:00:00'),
            $category,
            [],
            true,
            null,
            null,
            true
        );

        $this->happeningRepository->add($happening);

        return $happening;
    }
}
