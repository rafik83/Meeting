<?php

namespace Proximum\Vimeet\Application\Components\Meeting;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\View\Meeting\RequestView;

/**
 * Class RequestViewsBuilder
 *
 * @deprecated
 */
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
     * @param User      $user
     * @param Sheet     $sheet
     * @param string    $locale
     *
     * @return RequestView[]
     */
    public function generate(array $requests, User $user, Sheet $sheet, $locale)
    {
        return array_map(function (Request $request) use ($user, $sheet, $locale) {
            return $this->requestViewBuilder->generate($request, $user, $sheet, $locale);
        }, $requests);
    }
}
