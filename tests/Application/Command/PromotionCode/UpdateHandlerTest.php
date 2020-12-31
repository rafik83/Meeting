<?php

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class UpdateHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $event = EventFactory::createEvent();

        // Expected
        $promotionCode = new PromotionCode(
            $event,
            'promotionCodeTitle',
            'TESTCODE',
            10
        );

        $translations = [
            'fr' => [
                'label' => 'label fr',
                'description' => 'description fr',
            ],
            'en' => [
                'label' => 'label en',
                'description' => 'description en',
            ],
        ];

        $update = new Update($promotionCode);
        $update->title = 'Updated title';
        $update->translations = $translations;

        $updatedPromotionCode = new PromotionCode(
            $event,
            'Updated title',
            'TESTCODE',
            10
        );
        $updatedPromotionCode->translate('fr', 'label fr', 'description fr');
        $updatedPromotionCode->translate('en', 'label en', 'description en');

        $promotionCodeRepository = $this->prophesize(PromotionCodeRepositoryInterface::class);
        $promotionCodeRepository->set($updatedPromotionCode)->shouldBeCalled();

        $promotionCodeFactory = $this->prophesize(PromotionCodeFactory::class);
        $promotionCodeFactory
            ->update(
                $promotionCode,
                'Updated title',
                'TESTCODE',
                10,
                null,
                $translations,
                []
            )
            ->shouldBeCalled()
            ->willReturn($updatedPromotionCode);

        $handler = new UpdateHandler(
            $promotionCodeFactory->reveal(),
            $promotionCodeRepository->reveal()
        );

        $handler->handle($update);
    }
}
