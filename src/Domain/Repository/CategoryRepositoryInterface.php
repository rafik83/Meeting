<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PaginatedResult;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\View\CategoryView;

interface CategoryRepositoryInterface
{
    /**
     * @param int    $page
     * @param int    $limit
     * @param int    $eventId
     * @param string $locale
     *
     * @return PaginatedResult
     */
    public function paginate($page, $limit, $eventId, $locale);

    /**
     * @param Category $category
     */
    public function set(Category $category);

    /**
     * @param Category $category
     */
    public function add(Category $category);

    /**
     * @param Event  $event
     * @param User   $user
     * @param string $locale
     *
     * @return CategoryView[]
     */
    public function getCategoryViewsByEventAndUser(Event $event, User $user, $locale);

    /**
     * @param Event  $event
     * @param string $locale
     *
     * @return Category[]
     */
    public function getCategoriesByEvent(Event $event, $locale);

    /**
     * @param int    $id
     * @param string $locale
     *
     * @return CategoryView
     */
    public function getCategoryViewById($id, $locale);
}
