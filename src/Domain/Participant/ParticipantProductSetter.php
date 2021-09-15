<?php

namespace Proximum\Vimeet\Domain\Participant;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Participant\ProductSetOnParticipantEvent;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ParticipantProductSetter
{
    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        ParticipantRepositoryInterface $participantRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->participantRepository = $participantRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function setProductOnParticipant(Participant $participant, ?Product $product = null): void
    {
        $previousProduct = $participant->getParticipantProduct();

        $hasNewProduct = null === $previousProduct && null !== $product;
        $removeProduct = null !== $previousProduct && null === $product;
        $newProductIsDifferent = $previousProduct instanceof Product
            && $product instanceof Product
            && $previousProduct->getId() !== $product->getId()
        ;

        if ($hasNewProduct || $removeProduct || $newProductIsDifferent) {
            $participant->setParticipantProduct($product);
            $this->participantRepository->set($participant);

            $event = new ProductSetOnParticipantEvent($participant);
            $this->eventDispatcher->dispatch(Events::PARTICIPANT_PRODUCT_SET, $event);
        }
    }
}
