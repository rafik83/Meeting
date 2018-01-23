<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\User\Agenda\Version;

class VersionNormalizer
{
    /** @var RequestVersionNormalizer */
    private $requestVersionNormalizer;

    /**
     * @param RequestVersionNormalizer $requestVersionNormalizer
     */
    public function __construct(RequestVersionNormalizer $requestVersionNormalizer)
    {
        $this->requestVersionNormalizer = $requestVersionNormalizer;
    }

    /**
     * @param array $requests
     *
     * @return array
     */
    public function normalize(array $requests): array
    {
        $normalizedVersion = [];

        foreach ($requests as $request) {
            $normalizedVersion[$request->getId()] = $this->requestVersionNormalizer->normalize($request);
        }

        return $normalizedVersion;
    }
}
