<?php


namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener\Product;

use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Product\ProductUpdatedEvent;
use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Domain\Unavailability\SystemGenerator\GenerateParticipantProductSystemUnavailabilities;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductUpdatedEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var GenerateParticipantProductSystemUnavailabilities
     */
    private $generateParticipantProductSystemUnavailabilities;

    public function __construct(
        GenerateParticipantProductSystemUnavailabilities $generateParticipantProductSystemUnavailabilities
    ) {
        $this->generateParticipantProductSystemUnavailabilities = $generateParticipantProductSystemUnavailabilities;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::PRODUCT_UPDATED => 'onProductUpdated',
        ];
    }

    public function onProductUpdated(ProductUpdatedEvent $updatedEvent)
    {
        if (!$updatedEvent->product->isParticipant()) {
            return;
        }

        $IdPreviousArray = $this->setIdTimeRangeArray($updatedEvent->previousAvailabilityTimeRanges);
        $IdProductArray = $this->setIdTimeRangeArray($updatedEvent->product->getAvailabilityTimeRanges());

        if ($IdProductArray === $IdPreviousArray) {
            return;
        }

        ($this->generateParticipantProductSystemUnavailabilities)($updatedEvent->product);
    }

    /**
     * @param array $timeRangeArrayObjects
     *
     * @return bool
     */
    private function setIdTimeRangeArray(array $timeRangeArrayObjects)
    {
        $availabilityTimeRangeIds = array_map(
            function (AvailabilityTimeRange $timeRange) {

                return $timeRange->getId();
            }
            , $timeRangeArrayObjects
        );
        sort($availabilityTimeRangeIds);

        return $availabilityTimeRangeIds;
    }
}
