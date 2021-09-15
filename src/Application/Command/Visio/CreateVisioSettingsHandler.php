<?php

namespace Proximum\Vimeet\Application\Command\Visio;

use Proximum\Vimeet\Domain\Model\Visio\VisioSettings;
use Proximum\Vimeet\Domain\Repository\Visio\VisioSettingsRepositoryInterface;

class CreateVisioSettingsHandler
{
    /** @var VisioSettingsRepositoryInterface */
    private $visioSettingsRepository;

    public function __construct(VisioSettingsRepositoryInterface $visioSettingsRepository)
    {
        $this->visioSettingsRepository = $visioSettingsRepository;
    }

    public function handle(CreateVisioSettings $createVisioSettings): VisioSettings
    {
        $visioSettings = $this->visioSettingsRepository->getByEvent($createVisioSettings->event);

        if (null !== $visioSettings) {
            return $visioSettings;
        }

        $visioSettings = new VisioSettings($createVisioSettings->event);

        $this->visioSettingsRepository->create($visioSettings);

        return $visioSettings;
    }
}
