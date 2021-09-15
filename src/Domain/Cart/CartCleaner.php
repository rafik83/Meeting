<?php

namespace Proximum\Vimeet\Domain\Cart;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;

/**
 * Remove other event product from cart
 * Fix issue jira#1375
 */
class CartCleaner
{
    /** @var CartManager */
    private $cartManager;

    /** @var CartRowRepositoryInterface */
    private $cartRowRepository;

    public function __construct(CartManager $cartManager, CartRowRepositoryInterface $cartRowRepository)
    {
        $this->cartManager = $cartManager;
        $this->cartRowRepository = $cartRowRepository;
    }

    public function handle(Sheet $sheet): void
    {
        $cart = $this->cartManager->getCart($sheet);

        foreach ($cart->getRows() as $cartRow) {
            if ($cartRow->getProduct()->getEvent()->getId() !== $sheet->getEvent()->getId()) {
                $this->cartRowRepository->delete($cartRow);
            }
        }
    }
}
