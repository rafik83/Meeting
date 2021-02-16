<?php

namespace Proximum\Vimeet\Application\Command\Package;

use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Repository\PackageRepositoryInterface;

class CreateHandler
{
    /** @var PackageRepositoryInterface */
    private $packageRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    /** @var array */
    private $defaultLabels;

    /**
     * @param PackageRepositoryInterface $packageRepository
     * @param \DateTimeInterface         $dateTime
     * @param array                      $defaultLabels
     */
    public function __construct(
        PackageRepositoryInterface $packageRepository,
        \DateTimeInterface $dateTime,
        array $defaultLabels
    ) {
        $this->packageRepository = $packageRepository;
        $this->dateTime          = $dateTime;
        $this->defaultLabels     = $defaultLabels;
    }

    /**
     * @param Create $create
     *
     * @return CreateResult
     */
    public function handle(Create $create)
    {
        $package = new Package($create->event, $create->title, $this->dateTime);

        foreach ($create->event->getLocales() as $locale) {
            $package->translate(
                $locale,
                isset($this->defaultLabels['plans'][$locale]) ? $this->defaultLabels['plans'][$locale] : '',
                isset($this->defaultLabels['participant_and_planning'][$locale]) ? $this->defaultLabels['participant_and_planning'][$locale] : '',
                isset($this->defaultLabels['options'][$locale]) ? $this->defaultLabels['options'][$locale] : ''
            );
        }

        $this->packageRepository->add($package);

        return new CreateResult($package);
    }
}
