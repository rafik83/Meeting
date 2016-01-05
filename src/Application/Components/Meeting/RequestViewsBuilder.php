<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\View\Meeting\RequestView;

class RequestViewsBuilder
{
    /**
     * @var RequestViewBuilder
     */
    private $requestViewBuilder;

    /**
     * RequestViewsBuilder constructor.
     *
     * @param RequestViewBuilder $requestViewBuilder
     */
    public function __construct(RequestViewBuilder $requestViewBuilder)
    {
        $this->requestViewBuilder = $requestViewBuilder;
    }

    /**
     * @param Request[] $requests
     *
     * @return RequestView[]
     */
    public function generate($requests)
    {
        return array_map(function ($request) {
            return $this->requestViewBuilder->generate($request);
        }, $requests);
    }
}
