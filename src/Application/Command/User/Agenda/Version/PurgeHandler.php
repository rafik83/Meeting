<?php

namespace Proximum\Vimeet\Application\Command\User\Agenda\Version;

use Proximum\Vimeet\Domain\Model\User\Agenda\Version;
use Proximum\Vimeet\Domain\Repository\User\Agenda\VersionRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\Generator;

class PurgeHandler
{
    /** @var \DateTimeInterface */
    public $dateTime;

    /** @var VersionRepositoryInterface */
    private $versionRepository;

    /** @var Generator */
    private $generator;

    /**
     * @param VersionRepositoryInterface $versionRepository
     * @param Generator                  $generator
     * @param \DateTimeInterface         $dateTime
     */
    public function __construct(
        VersionRepositoryInterface $versionRepository,
        Generator $generator,
        \DateTimeInterface $dateTime
    ) {
        $this->versionRepository = $versionRepository;
        $this->generator = $generator;
        $this->dateTime = $dateTime;
    }

    /**
     * @param Purge $purge
     */
    public function handle(Purge $purge)
    {
        $diff = $this->generator->generate($purge->event, $purge->user);
        $version = new Version($purge->event, $purge->user, $diff, $this->dateTime);

        $this->versionRepository->add($version);
    }
}
