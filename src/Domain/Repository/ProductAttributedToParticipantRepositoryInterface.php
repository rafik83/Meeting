<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\ProductAttributedToParticipant;
use Proximum\Vimeet\Domain\Model\User;

interface ProductAttributedToParticipantRepositoryInterface
{
    public function add(ProductAttributedToParticipant $productAttributedToParticipant): void;

    /**
     * @param Product       $product
     * @param Participant[] $participants
     *
     * @return ProductAttributedToParticipant[]
     */
    public function findByProductAndParticipants(Product $product, array $participants): array;

    /**
     * @param ProductAttributedToParticipant[] $productAttributedToParticipants
     */
    public function removeBatch(array $productAttributedToParticipants): void;

    /**
     * @param Participant $participant
     *
     * @return ProductAttributedToParticipant[]
     */
    public function findByParticipant(Participant $participant): array;

    /**
     * @param Participant[] $participants
     *
     * @return ProductAttributedToParticipant[]
     */
    public function findByParticipants(array $participants): array;

    /**
     * @param Participant $participant
     * @param Product[]   $products
     *
     * @return bool
     */
    public function participantHasAtLeastOneProduct(Participant $participant, array $products): bool;

    /**
     * @param User  $user
     * @param Event $event
     *
     * @return Product[]
     */
    public function findProductIdsAttributedByUserAndEvent(User $user, Event $event): array;
}
