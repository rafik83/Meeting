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
    /**
     * @param Event              $event
     * @param string             $title
     * @param \DateTimeInterface $createdAt
     *
     * @return ProductsSelectionTemplate
     */
    public function createFromEvent(Event $event, $title, \DateTimeInterface $createdAt)
    {
        $value = [
            'packages' => [
                'component' => 'object',
                'type'      => 'package',
                'config'    => [
                    'label'   => ['fr' => 'Formules'],
                    'enabled' => true,
                    'package' => [],
                ],
            ],
            'participants_planings' => [
                'component' => 'object',
                'type'      => 'participants_planings',
                'config'    => [
                    'label'       => ['fr' => 'Participants & Plannings'],
                    'enabled'     => true,
                    'participant' => null,
                    'planing'     => null,
                ],
            ],
            'options' => [
                'component' => 'object',
                'type'      => 'options',
                'config'    => [
                    'label'     => ['fr' => 'Options'],
                    'enabled'   => true,
                    'products'  => [],
                ],
            ]
        ];

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
