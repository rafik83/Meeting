<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Adapter;

interface EntityManagerAdapterInterface
{
    /**
     * @param $entity
     */
    public function persist($entity);

    /**
     * Flushes all changes to objects that have been queued up to now to the database.
     * This effectively synchronizes the in-memory state of managed objects with the
     * database.
     *
     * If an entity is explicitly passed to this method only this entity and
     * the cascade-persist semantics + scheduled inserts/removals are synchronized.
     *
     * @param null|object|array $entity
     */
    public function flush($entity = null);

    /**
     * Clears the EntityManager. All entities that are currently managed
     * by this EntityManager become detached.
     */
    public function clear();

    /**
     * Detaches an entity from the EntityManager, causing a managed entity to
     * become detached.  Unflushed changes made to the entity if any
     * (including removal of the entity), will not be synchronized to the database.
     * Entities which previously referenced the detached entity will continue to
     * reference it.
     *
     * @param object $entity The entity to detach.
     */
    public function detach($entity);

    public function beginTransaction();

    public function commit();

    public function rollback();
}
