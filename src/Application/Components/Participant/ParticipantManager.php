<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Participant;

use Proximum\Vimeet\Application\Components\Order\OrderManager;
use Proximum\Vimeet\Domain\Model\Order;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\CartRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class ParticipantManager
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    private $orderManager;

    /**
     * @param CartRepositoryInterface        $cartRepository
     * @param ParticipantRepositoryInterface $participantRepository
     * @param OrderManager                   $orderManager
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ParticipantRepositoryInterface $participantRepository,
        OrderManager $orderManager
    ) {
        $this->cartRepository        = $cartRepository;
        $this->participantRepository = $participantRepository;
        $this->orderManager          = $orderManager;
    }

    /**
     * Get the state (active) of a new Participant
     * FYI this function does not take into account the Participant
     * Included with another option, this feature need to be implemented
     * In another story
     *
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function getNewParticipantState(Sheet $sheet)
    {
        if (empty($sheet->getOrders())) {
            return false;
        }

        $numberOfParticipant = count($sheet->getParticipants());
        $freeParticipant     = $sheet->getType()->getFreeParticipant();

        foreach ($sheet->getTypePackageTemplate() as $stepKey => $step) {
            foreach ($step['template'] as $productKey => $product) {
                if (isset($product['type']) && $product['type'] === 'lib_participant') {
                    if (isset($sheet->getPackageData()[$stepKey][$productKey]['participant'])
                        && $sheet->getPackageData()[$stepKey][$productKey]['participant'] === true
                        && isset($sheet->getPackageData()[$stepKey][$productKey]['quantity'])
                    ) {
                        return $sheet->getPackageData()[$stepKey][$productKey]['quantity'] + $freeParticipant > $numberOfParticipant;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param Sheet $sheet
     *
     * @return bool
     */
    public function canBuyParticipant(Sheet $sheet)
    {
        return !($this->getRemainingPossibleParticipant($sheet) === 0
            || $this->getRemainingPossibleParticipant($sheet) < 0
            || $this->getRemainingPossibleParticipantToBuy($sheet) === 0
            || $this->getRemainingPossibleParticipantToBuy($sheet) < 0
        );
    }

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function canAddParticipant(Sheet $sheet)
    {
        if ($this->getRemainingPossibleParticipant($sheet) === 0) {
            return 0;
        }

        $count = $this->getBoughtParticipantWithCart($sheet) + $sheet->getType()->getFreeParticipant() - count($sheet->getParticipants());

        return max([0, $count]);
    }

    /**
     * @param Sheet $sheet
     *
     * @return array
     */
    public function canBuyOrAddParticipant(Sheet $sheet)
    {
        return [
            'canAddParticipant' => $this->canAddParticipant($sheet),
        ];
    }

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function getBuyQuantityParticipant(Sheet $sheet)
    {
        $max  = $sheet->getType()->getMaxParticipant();
        $free = $sheet->getType()->getFreeParticipant();

        return intval($max - $free);
    }

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function getAddedBoughtParticipant(Sheet $sheet)
    {
        $freeParticipant = $sheet->getType()->getFreeParticipant();
        $participants    = count($sheet->getParticipants());

        return $participants - $freeParticipant;
    }

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function getBoughtParticipantWithCart(Sheet $sheet)
    {
        return $this->getBoughtParticipant($sheet) + $this->getCartParticipant($sheet);
    }

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function getBoughtParticipant(Sheet $sheet)
    {
        $bought = 0;

        $packageData = $sheet->getPackageData();

        foreach ($sheet->getTypePackageTemplate() as $blockKey => $block) {
            foreach ($block['template'] as $elementKey => $element) {
                if ($element['type'] === 'lib_participant' && isset($packageData[$blockKey][$elementKey])) {
                    if (isset($packageData[$blockKey][$elementKey]['quantity'])
                        && isset($packageData[$blockKey][$elementKey]['participant'])
                        && true === $packageData[$blockKey][$elementKey]['participant']
                    ) {
                        $bought += intval($packageData[$blockKey][$elementKey]['quantity']);
                    }
                }
            }
        }

        return $bought;
    }

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function getCartParticipant(Sheet $sheet)
    {
        $numberOfParticipant = 0;
        $cart                = $this->cartRepository->findBySheet($sheet);

        if (null !== $cart) {
            foreach ($cart->getTemplate() as $blockKey => $block) {
                foreach ($block['template'] as $elementKey => $element) {
                    if ($element['type'] === 'lib_participant' && isset($cart->getData()[$blockKey][$elementKey])) {
                        if (isset($cart->getData()[$blockKey][$elementKey]['quantity'])
                            && isset($cart->getData()[$blockKey][$elementKey]['participant'])
                            && true === $cart->getData()[$blockKey][$elementKey]['participant']
                        ) {
                            $numberOfParticipant += intval($cart->getData()[$blockKey][$elementKey]['quantity']);
                        }
                    }
                }
            }
        }

        return $numberOfParticipant;
    }

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function getRemainingPossibleParticipantToBuy(Sheet $sheet)
    {
        $max    = $sheet->getType()->getMaxParticipant();
        $free   = $sheet->getType()->getFreeParticipant();
        $bought = $this->getBoughtParticipant($sheet);

        return intval($max - ($bought + $free));
    }

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    public function getRemainingPossibleParticipant(Sheet $sheet)
    {
        $max   = $sheet->getType()->getMaxParticipant();
        $added = count($sheet->getParticipants());

        return $max - $added;
    }

    /**
     * @param Sheet $sheet
     *
     * @return int|null
     */
    public function getParticipantPrice(Sheet $sheet)
    {
        $packageData = $sheet->getTypePackageTemplate();

        foreach ($packageData as $template) {
            foreach ($template['template'] as $block) {
                if ($block['type'] === 'lib_participant') {
                    return isset($block['unitPrice']) ? $block['unitPrice'] : null;
                }
            }
        }

        return;
    }

    public function convertInactiveParticipantAfterOrderCreation(Sheet $sheet, Order $order)
    {
        $numberOfParticipantFromOrder = $this->getNumberOfParticipantFromOrder($order);
        $participantInactive          = $this->participantRepository->getInactiveParticipantForSheet($sheet);

        if (count($sheet->getParticipants()) > $sheet->getType()->getFreeParticipant() && !empty($participantInactive)) {
            foreach ($participantInactive as $participant) {
                if ($numberOfParticipantFromOrder > 0) {
                    $participant->setActive(true);
                    $participant->setOrder($order);
                    $this->participantRepository->set($participant);
                    $numberOfParticipantFromOrder--;
                } else {
                    break;
                }
            }
        }
    }

    /**
     * @param Order $order
     *
     * @return int
     */
    private function getNumberOfParticipantFromOrder(Order $order)
    {
        $template = $order->getPackageTemplate();
        $data     = $order->getPackageData();

        if (!empty($template)) {
            foreach ($template as $blockKey => $block) {
                foreach ($block['template'] as $productKey => $product) {
                    if (isset($product['type'])
                        && 'lib_participant' ===  $product['type']
                        && isset($data[$blockKey][$productKey]['participant'])
                        && true === $data[$blockKey][$productKey]['participant']
                        && isset($data[$blockKey][$productKey]['quantity'])
                    ) {
                        return $data[$blockKey][$productKey]['quantity'];
                    }
                }
            }
        }

        return 0;
    }

    /**
     * @param Sheet $sheet
     *
     * @return null|Order
     */
    public function findOrderToAttach(Sheet $sheet)
    {
        $orders = $sheet->getOrders();

        foreach ($orders as $order) {
            $participantBought           = $this->orderManager->getParticipantBoughtForOrder($order);
            $numberOfParticipantAttached = $this->countNumberOfParticipantAttachedToAnOrder($sheet, $order);

            if ($participantBought > $numberOfParticipantAttached) {
                return $order;
            }
        }

        return null;
    }

    private function countNumberOfParticipantAttachedToAnOrder(Sheet $sheet, Order $order)
    {
        $numberOfParticipantAttached = 0;
        foreach ($sheet->getParticipants() as $participant) {
            if (null !== $participant->getOrder() && $order == $participant->getOrder()) {
                $numberOfParticipantAttached++;
            }
        }

        return $numberOfParticipantAttached;
    }

    /**
     * A user is allowed to edit a participant if he is the sheet owner or if he is the participant himself
     *
     * @param Sheet       $sheet
     * @param Participant $participant
     * @param User        $user
     *
     * @return bool
     */
    public function isUserAllowedToEditParticipant(Sheet $sheet, Participant $participant, User $user)
    {
        return $sheet->hasParticipant($participant) && ($sheet->getUserParticipant($user)->isOwner() || $participant->getUser() === $user);
    }

    /**
     * A user is allowed to delete a participant if he is the sheet owner and the participant is not the sheet owner
     *
     * @param Sheet       $sheet
     * @param Participant $participant
     * @param User        $user
     *
     * @return bool
     */
    public function isUserAllowedToDeleteParticipant(Sheet $sheet, Participant $participant, User $user)
    {
        return $sheet->hasParticipant($participant) && $sheet->getUserParticipant($user)->isOwner() && !$participant->isOwner();
    }
}
