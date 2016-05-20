<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Template\ProductsSelection;

use Proximum\Vimeet\Domain\Model\Template\ProductsSelectionTemplate;
use Proximum\Vimeet\Domain\Repository\Template\ProductsSelectionTemplateRepositoryInterface;

class CreateHandler
{
    private $stepsLabel = [
        'Formules',
        'Participants & Plannings',
        'Options',
    ];

    /**
     * @var ProductsSelectionTemplateRepositoryInterface
     */
    private $productsSelectionTemplateRepository;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * CreateForEventHandler constructor.
     *
     * @param ProductsSelectionTemplateRepositoryInterface $productsSelectionTemplateRepository
     * @param \DateTimeInterface                           $dateTime
     */
    public function __construct(
        ProductsSelectionTemplateRepositoryInterface $productsSelectionTemplateRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->productsSelectionTemplateRepository = $productsSelectionTemplateRepository;
        $this->dateTime                            = $dateTime;
    }

    /**
     * @param Create $create
     *
     * @return ProductsSelectionTemplate
     */
    public function handle(Create $create)
    {
        $value = [];
        $uid   = uniqid();

        // create 3 blocks
        for ($step = 0; $step < 3; $step++) {
            $labels = [];
            foreach ($create->event->getLocales() as $locale) {
                $labels[$locale] = $this->stepsLabel[$step];
            }

            $value[sha1($step . $uid)] = [
                'component' => 'block',
                'type'      => '12',
                'config'    => [
                    'label'     => $labels,
                    'enabled'   => true,
                ],
                'children'  => [],
            ];
        }

        $template = new ProductsSelectionTemplate(
            $create->title,
            $value,
            $create->event->getLocales(),
            $create->event->getFallback(),
            $this->dateTime
        );

        $template->setEvent($create->event);

        $this->productsSelectionTemplateRepository->add($template);

        return $template;
    }
}
