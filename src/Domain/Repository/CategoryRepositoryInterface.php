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
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\View\CategoryView;

interface CategoryRepositoryInterface
{
    /**
     * @param Event|int $event
     * @param User|int  $user
     * @param string    $locale
     *
     * @return CategoryView[]
     */
    public function getCategoryViewsByEventAndUser($event, $user, $locale);

    /**
     * @param Event $event
     *
     * @return Category[]
     */
    public function getCategoriesByEvent(Event $event);

    /**
     * @param int    $id
     * @param string $locale
     *
     * @return CategoryView
     */
    public function getCategoryViewById($id, $locale);
}
