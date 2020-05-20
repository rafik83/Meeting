<?php


namespace Proximum\Vimeet\Application\Components\Visio;

use Proximum\Vimeet\Application\Command\Visio\CreateVisioSettings;
use Proximum\Vimeet\Application\Command\Visio\CreateVisioSettingsHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Visio\VisioSettings;
use Proximum\Vimeet\Domain\Repository\Visio\VisioSettingsRepositoryInterface;

class VisioSettingsRetriever
{
    /** @var VisioSettingsRepositoryInterface */
    private $visioSettingsRepository;

    /** @var CreateVisioSettingsHandler */
    private $createVisioSettingsHandler;

    public function __construct(
        VisioSettingsRepositoryInterface $visioSettingsRepository,
        CreateVisioSettingsHandler $createVisioSettingsHandler
    ) {
        $this->visioSettingsRepository = $visioSettingsRepository;
        $this->createVisioSettingsHandler = $createVisioSettingsHandler;
    }

    public function get(Event $event): VisioSettings
    {
        $visioSettings = $this->visioSettingsRepository->getByEvent($event);

        if (null !== $visioSettings) {
            return $visioSettings;
        }

        return $this->createVisioSettingsHandler->handle(new CreateVisioSettings($event));
    }
}
