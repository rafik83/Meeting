<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Template;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\ProductsSelectionTemplate;

class ProductsSelectionTemplateFactory
{
    private $stepsLabel = [
        'Formules',
        'Participants & Plannings',
        'Options',
    ];

    /**
     * @var string
     */
    private $uid;

    /**
     * ProductsSelectionTemplateFactory constructor.
     */
    public function __construct()
    {
        $this->uid = uniqid();
    }

    /**
     * @param Event              $event
     * @param string             $title
     * @param \DateTimeInterface $createdAt
     *
     * @return ProductsSelectionTemplate
     */
    public function createFromEvent(Event $event, $title, \DateTimeInterface $createdAt)
    {
        $value = [];

        // create 3 blocks
        for ($step = 0; $step < 3; $step++) {
            $labels = [];
            foreach ($event->getLocales() as $locale) {
                $labels[$locale] = $this->stepsLabel[$step];
            }

            $value[sha1($step . $this->uid)] = [
                'component' => 'block',
                'type'      => '12',
                'config'    => [
                    'label'     => $labels,
                    'enabled'   => true,
                ],
                'children'  => [],
            ];
        }

        return new ProductsSelectionTemplate(
            $event,
            $title,
            $value,
            $event->getLocales(),
            $event->getFallback(),
            $createdAt
        );
    }
}
