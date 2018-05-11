<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\ProductAttributedToParticipant;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\ProductAttributedToParticipant;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;

class ProductAttributedToParticipantSetter
{
    /** @var ProductAttributedToParticipantRepositoryInterface */
    private $productAttributedToParticipantRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        ProductAttributedToParticipantRepositoryInterface $productAttributedToParticipantRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->productAttributedToParticipantRepository = $productAttributedToParticipantRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * @param Product       $product
     * @param Participant[] $sheetParticipants
     * @param Participant[] $participantsWithAttributedProduct
     */
    public function attributeProductToParticipantsAndRemoveThoseNoLongerNeeded(
        Product $product,
        array $sheetParticipants,
        array $participantsWithAttributedProduct
    ): void {
        $productAttributedToParticipantsIndexedByParticipantId = $this
            ->getProductAttributedToParticipantsIndexedByParticipantId($product, $sheetParticipants);

        $this->removeNoLongerNeededProductAttributedToParticipant(
            $participantsWithAttributedProduct,
            $productAttributedToParticipantsIndexedByParticipantId
        );

        $this->addProductAttributedToParticipants(
            $product,
            $participantsWithAttributedProduct,
            $productAttributedToParticipantsIndexedByParticipantId
        );
    }

    private function attributeProductToParticipant(Product $product, Participant $participant): void
    {
        $this->productAttributedToParticipantRepository->add(
            new ProductAttributedToParticipant($product, $participant, $this->dateTime)
        );
    }

    /**
     * @param Product                          $product
     * @param Participant[]                    $participantsWithAttributedProduct
     * @param ProductAttributedToParticipant[] $productAttributedToParticipantsIndexedByParticipantId
     */
    private function addProductAttributedToParticipants(
        Product $product,
        array &$participantsWithAttributedProduct,
        array &$productAttributedToParticipantsIndexedByParticipantId
    ): void {
        foreach ($participantsWithAttributedProduct as $participant) {
            if (array_key_exists($participant->getId(), $productAttributedToParticipantsIndexedByParticipantId)) {
                continue;
            }

            $this->attributeProductToParticipant($product, $participant);
        }
    }

    /**
     * @param Participant[] $participantsWithAttributedProduct
     * @param array         $productAttributedToParticipantsIndexedByParticipantId
     */
    private function removeNoLongerNeededProductAttributedToParticipant(
        array &$participantsWithAttributedProduct,
        array &$productAttributedToParticipantsIndexedByParticipantId
    ): void {
        $productAttributedToParticipantsToRemove = [];

        foreach ($productAttributedToParticipantsIndexedByParticipantId as $participantId => $productAttributedToParticipant) {
            if (!$this->hasParticipantId($participantId, $participantsWithAttributedProduct)) {
                $productAttributedToParticipantsToRemove[] = $productAttributedToParticipant;
            }
        }

        if (!empty($productAttributedToParticipantsToRemove)) {
            $this->productAttributedToParticipantRepository->removeBatch($productAttributedToParticipantsToRemove);
        }
    }

    /**
     * @param int           $participantId
     * @param Participant[] $participantsWithAttributedProduct
     *
     * @return bool
     */
    private function hasParticipantId(int $participantId, array &$participantsWithAttributedProduct): bool
    {
        foreach ($participantsWithAttributedProduct as $participant) {
            if ($participantId === $participant->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Product $product
     * @param array   $participants
     *
     * @return ProductAttributedToParticipant[] indexed by Participant id
     */
    private function getProductAttributedToParticipantsIndexedByParticipantId(
        Product $product,
        array &$participants
    ): array {
        $productAttributedToParticipants = $this
            ->productAttributedToParticipantRepository
            ->findByProductAndParticipants($product, $participants);

        $productAttributedToParticipantsIndexedByParticipantId = [];

        foreach ($productAttributedToParticipants as $productAttributedToParticipant) {
            $participantId = $productAttributedToParticipant->getParticipant()->getId();
            $productAttributedToParticipantsIndexedByParticipantId[$participantId] = $productAttributedToParticipant;
        }

        return $productAttributedToParticipantsIndexedByParticipantId;
    }
}
