<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\TipFactory;

class TipManager
{
    /** @var TipRepositoryInterface */
    private $tipRepository;

    /** @var EventManager */
    private $eventManager;

    /** @var TypeManager */
    private $typeManager;

    /**
     * @param TipRepositoryInterface $tipRepository
     * @param EventManager           $eventManager
     * @param TypeManager            $typeManager
     */
    public function __construct(
        TipRepositoryInterface $tipRepository,
        EventManager $eventManager,
        TypeManager $typeManager
    ) {
        $this->tipRepository = $tipRepository;
        $this->eventManager  = $eventManager;
        $this->typeManager   = $typeManager;
    }

    /**
     * @param string|null $title
     *
     * @return Tip
     */
    public function create($title = null)
    {
        $tip = TipFactory::createTip($title);

        $this->tipRepository->add($tip);

        return $tip;
    }

    /**
     * @param string $tipTitle
     * @param Type   $type
     *
     * @return Tip
     */
    public function createForGivenType(string $tipTitle, Type $type)
    {
        $event = $type->getEvent();
        $tip = TipFactory::createTip($tipTitle, $event);
        $tip->setType($type);

        $this->tipRepository->add($tip);

        return $tip;
    }

    /**
     * @param string $tipTitle
     * @param string $eventTitle
     *
     * @return Tip
     */
    public function createForEvent($tipTitle, $eventTitle)
    {
        $event = $this->eventManager->create($eventTitle);
        $type  = $this->typeManager->create($event, 'Type 1');
        $tip   = TipFactory::createTip($tipTitle, $event);

        $tip->setType($type);

        $this->tipRepository->add($tip);

        return $tip;
    }

    /**
     * @param string $tipTitle
     * @param Type   $type
     * @param array  $contexts
     *
     * @return Tip
     */
    public function affectToType($tipTitle, Type $type, array $contexts)
    {
        $tip = TipFactory::createTip($tipTitle, $type->getEvent(), $contexts, $type->getEvent()->getLocales());
        $tip->setType($type);
        $this->tipRepository->add($tip);

        return $tip;
    }

    /**
     * @param Tip $tip
     */
    public function affectOnCatalog(Tip $tip)
    {
        $tip->update(
            $tip->getTitle(),
            $tip->isOnMeetingManagement(),
            true,
            $tip->isOnPrintPlanning(),
            $tip->isOnSheet(),
            $tip->isOnAgenda(),
            $tip->isOnPackage(),
            $tip->isOnContacts(),
            $tip->isOnProgram(),
            $tip->isOnConfirmationPhone(),
            $tip->isOnNetworking()
        );

        $this->tipRepository->set($tip);
    }

    /**
     * @param Tip $tip
     */
    public function affectOnMeetingManagement(Tip $tip)
    {
        $tip->update(
            $tip->getTitle(),
            true,
            $tip->isOnCatalog(),
            $tip->isOnPrintPlanning(),
            $tip->isOnSheet(),
            $tip->isOnAgenda(),
            $tip->isOnPackage(),
            $tip->isOnContacts(),
            $tip->isOnProgram(),
            $tip->isOnConfirmationPhone(),
            $tip->isOnNetworking()
        );

        $this->tipRepository->set($tip);
    }

    /**
     * @param Tip    $tip
     * @param string $locale
     * @param string $title
     */
    public function translateTitle(Tip $tip, string $locale, string $title)
    {
        $tip->translate($locale, $title, $tip->getTranslationContent($locale), new \DateTime());

        $this->tipRepository->set($tip);
    }
}
