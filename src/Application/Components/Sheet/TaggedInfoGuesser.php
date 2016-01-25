<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Sheet;

use Proximum\Vimeet\Application\Components\Sheet\Template\TypeInterface;
use Proximum\Vimeet\Application\Components\Sheet\Template\TemplateFactory;
use Proximum\Vimeet\Domain\Model\Order;

class TaggedInfoGuesser
{
    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * TaggedInfoGuesser constructor.
     *
     * @param TemplateFactory $templateFactory
     */
    public function __construct(TemplateFactory $templateFactory)
    {
        $this->templateFactory = $templateFactory;
    }

    /**
     * @param array  $template
     * @param array  $data
     * @param string $tag
     *
     * @return array
     */
    public function guess(array $template, array $data, $tag)
    {
        $types  = $this->templateFactory->createTemplateFromArray($template)->getTypesByTag($tag);
        $values = array_map(function (TypeInterface $type) use ($data) {
            $groupName = $type->getGroup()->getName();
            $typeName  = $type->getName();

            return $groupName === 'default' ?
                (isset($data[$typeName]) ? $data[$typeName] : null) :
                (isset($data[$groupName][$typeName]) ? $data[$groupName][$typeName] : null);
        }, $types);

        return $values;
    }

    /**
     * @param Order  $order
     * @param string $tag
     *
     * @return array
     */
    public function guessFromOrder(Order $order, $tag)
    {
        return $this->guess($order->getBillingTemplate(), $order->getBillingData(), $tag);
    }
}
