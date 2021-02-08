<?php

namespace Proximum\Vimeet\Behat\Context\Domain\Promotion;

use Behat\Behat\Context\Context;
use Proximum\Vimeet\Behat\Context\Storage\StorageInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

class PromotionContext implements Context
{
    private StorageInterface $storage;
    private PromotionCodeRepositoryInterface $promotionCodeRepository;

    public function __construct(StorageInterface $storage, PromotionCodeRepositoryInterface $promotionCodeRepository)
    {
        $this->storage = $storage;
        $this->promotionCodeRepository = $promotionCodeRepository;
    }

    /**
     * @Given there is a promotion :title with code :code for this product options
     */
    public function thereIsAPromotionForProductOptionWithCode(string $title, string $code)
    {
        $event = $this->storage->get('event');
        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $options = $this->storage->get('options');
        if (null === $options) {
            throw new \InvalidArgumentException('Missing Product (product options)');
        }

        foreach ($options as $option) {
            $this->createPromotionCode($event, $option, $title, $code);
        }
    }

    /**
     * @Given there is a promotion :title with code :code for this product participant
     */
    public function thereIsAPromotionParticipantWithCode(string $title, string $code)
    {
        $event = $this->storage->get('event');
        if (null === $event) {
            throw new \InvalidArgumentException('Missing Event');
        }

        $product = $this->storage->get('productParticipant');
        if (null === $product) {
            throw new \InvalidArgumentException('Missing Product (product participant)');
        }

        $this->createPromotionCode($event, $product, $title, $code);
    }

    private function createPromotionCode(Event $event, Product $product, string $title, string $code): void
    {
        $promotionCode = new PromotionCode($event, $title, $code);
        $promotionCode->setPromotion($product, Promotion::TYPE_VALUE_OFF, 10, 1);
        $promotionCode->translate('fr', $title, 'description');

        $this->promotionCodeRepository->add($promotionCode);
    }
}
