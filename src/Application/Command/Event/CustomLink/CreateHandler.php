<?php

namespace Proximum\Vimeet\Application\Command\Event\CustomLink;

use Proximum\Vimeet\Application\Command\StaticFormulation as StaticFormulationCommand;
use Proximum\Vimeet\Domain\Model\Event\CustomLink;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Repository\Event\CustomLinkRepositoryInterface;
use Proximum\Vimeet\Domain\StaticFormulation\Constant;

class CreateHandler
{
    private CustomLinkRepositoryInterface $customLinkRepository;
    private StaticFormulationCommand\CreateHandler $staticFormulationCreateHandler;

    public function __construct(
        CustomLinkRepositoryInterface $customLinkRepository,
        StaticFormulationCommand\CreateHandler $staticFormulationCreateHandler
    ) {
        $this->customLinkRepository = $customLinkRepository;
        $this->staticFormulationCreateHandler = $staticFormulationCreateHandler;
    }

    public function handle(Create $command)
    {
        $staticFormulation = $this->createStaticFormulation($command);

        $customLink = new CustomLink(
            $command->event,
            $staticFormulation,
            $command->url,
            $command->iconName,
            $command->iconColor,
            $command->labelColor,
            $command->buttonColor,
            $command->priority
        );

        $this->customLinkRepository->add($customLink);
    }

    protected function createStaticFormulation(Create $command): StaticFormulation
    {
        $translations = [];
        foreach ($command->translatedLabels as $locale => $array) {
            $translations[$locale] = $array['title'];
        }

        $staticFormulationCreateCommand = new StaticFormulationCommand\Create(
            $command->event,
            Constant::STATIC_FORMULATION_KEY_CUSTOM_LINK,
            $translations
        );
        $staticFormulationCreateCommand->types = $command->types;

        return $this->staticFormulationCreateHandler->handle($staticFormulationCreateCommand);
    }
}
