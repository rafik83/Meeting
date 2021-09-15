<?php

namespace Proximum\Vimeet\Application\View\Happening\Notification;

class NotificationView
{
        /** @var string */
        public $providerUrl;

        /** @var string */
        public $subscriberKey;

        public function __construct(string $providerUrl, string $subscriberKey)
        {
            $this->providerUrl = $providerUrl;
            $this->subscriberKey = $subscriberKey;
        }
}
