<?php

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Command\Command;
use Proximum\Vimeet\Application\View\Package\ParticipantProductView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;

class Add implements Command
{
    /** @var Sheet */
    public $sheet;

    /** @var string */
    public $locale;

    /** @var string */
    public $firstName;

    /** @var string */
    public $lastName;

    /** @var string */
    public $email;

    /** @var bool */
    public $owner;

    /** @var User */
    public $adder;

    /**
     * ParticipantProductView selected to add the new participant
     * This product can be null as the package is not always passable
     *
     * @var ParticipantProductView|null
     */
    public $product;

    /** @var bool */
    public $needToSelectProduct;

    /**
     * @param Sheet                    $sheet
     * @param string                   $locale
     * @param User                     $adder
     * @param ParticipantProductView[] $products
     */
    public function __construct(
        Sheet $sheet,
        $locale,
        User $adder,
        array $products = []
    ) {
        $this->sheet  = $sheet;
        $this->locale = $locale;
        $this->owner  = false;
        $this->adder  = $adder;

        $productSelected = null;
        if (1 === count($products)) {
            $product = reset($products);

            if (false !== $product && $product->isBuyable) {
                $productSelected = $product;
            }
        }

        $this->product = $productSelected;
        $this->needToSelectProduct = count($products) >= 1;
    }
}
