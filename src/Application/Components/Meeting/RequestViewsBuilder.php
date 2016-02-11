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
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
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
     * @param User  $user
     * @param Sheet $sheet
     *
     * @return RequestView[]
     */
    public function generate(array $requests, User $user, Sheet $sheet)
    {
        return array_map(function (Request $request) use ($user, $sheet) {
            return $this->requestViewBuilder->generate($request, $user, $sheet);
        }, $requests);
    }
}
