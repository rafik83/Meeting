<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Components\Package\ProductByParticipantCartGetter;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Repository\ProductRepositoryInterface;

class UpdateParticipantProductQuantityHandler
{
    /** @var CartManager */
    private $cartManager;

    /** @var ProductByParticipantCartGetter */
    private $productByParticipantCartGetter;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @param CartManager                    $cartManager
     * @param ProductByParticipantCartGetter $productByParticipantCartGetter
     * @param ProductRepositoryInterface     $productRepository
     */
    public function __construct(
        CartManager $cartManager,
        ProductByParticipantCartGetter $productByParticipantCartGetter,
        ProductRepositoryInterface $productRepository
    ) {
        $this->cartManager = $cartManager;
        $this->productByParticipantCartGetter = $productByParticipantCartGetter;
        $this->productRepository = $productRepository;
    }

    /**
     * @param UpdateParticipantProductQuantity $command
     */
    public function handle(UpdateParticipantProductQuantity $command)
    {
        $product = $this->productRepository->findById($command->productId);

        if (null === $product) {
            throw new \InvalidArgumentException('Product not found');
        }

        $cart = $this->cartManager->getCart($command->sheet);
        $productParticipants = $this->productByParticipantCartGetter->getFromCart($cart);

        $productParticipants[$command->participant->getId()] = $product;

        $this->cartManager->updateParticipantsQuantity($cart, $productParticipants);
        $this->cartManager->save($cart);
    }
}
