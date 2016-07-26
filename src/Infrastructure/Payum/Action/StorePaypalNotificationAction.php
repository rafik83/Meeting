<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Notify;
use Payum\Paypal\ExpressCheckout\Nvp\Api;

class StorePaypalNotificationAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request)
    {
        $token = $request->getToken();
        $token->getGatewayName();

        $getHttpRequest = new GetHttpRequest();

        $this->gateway->execute($getHttpRequest);

        dump(Api::PAYMENTSTATUS_COMPLETED === $getHttpRequest->request['payment_status']);

        dump($request);
        exit;
    }

    /**
     * @param mixed $request
     *
     * @return boolean
     */
    public function supports($request)
    {
        //dump($request);
        //exit;
        return $request instanceof Notify;
    }
}
