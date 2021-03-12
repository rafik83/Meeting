<?php


namespace Proximum\Vimeet\Ui\Helper;


use Symfony\Component\HttpFoundation\Request;

class RequestHelper
{
    public static function getRelativeUri(Request $request): string
    {
        if (null !== $qs = $request->getQueryString()) {
            $qs = '?'.$qs;
        }

        return $request->getBaseUrl().$request->getPathInfo().$qs;
    }
}
