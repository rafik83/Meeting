<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Tests\Application\Command\Happening\Category;

use Proximum\Vimeet\Application\Command\Happening\Category\Update;
use Proximum\Vimeet\Application\Command\Happening\Category\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Happening\CategoryTranslation;
use Proximum\Vimeet\Domain\Repository\Happening\CategoryRepositoryInterface;

class UpdateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        // Context
        $event = new Event();
        $event->setLocales(['fr', 'en']);

        // Current
        $category     = new Category($event, 'picto1', 2);
        $translation1 = new CategoryTranslation($category, 'fr', 'truc');
        $translation2 = new CategoryTranslation($category, 'en', 'trac');
        $category->setTranslation($translation1);
        $category->setTranslation($translation2);

        // Expected
        $expectedCategory     = new Category($event, 'picto2', 3);
        $expectedTranslation1 = new CategoryTranslation($expectedCategory, 'fr', 'troc');
        $expectedTranslation2 = new CategoryTranslation($expectedCategory, 'en', 'trec');
        $expectedCategory->setTranslation($expectedTranslation1);
        $expectedCategory->setTranslation($expectedTranslation2);

        // Mock
        $categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $categoryRepository->set($expectedCategory)->shouldBeCalled();

        // Command
        $update           = new Update($category);
        $update->picto    = 'picto2';
        $update->position = 3;
        $update->translations = [
            'fr' => ['title' => 'troc'],
            'en' => ['title' => 'trec'],
        ];

        $handler = new UpdateHandler($categoryRepository->reveal());
        $handler->handle($update);
    }
}
