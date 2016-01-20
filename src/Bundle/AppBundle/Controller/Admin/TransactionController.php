<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Controller\Admin;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Transaction;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class TransactionController extends Controller
{
    public function createAction(Request $request, Sheet $sheet)
    {
        return $this->render('VimeetAppBundle:Admin/Transaction:create.html.twig', [

        ]);
    }

    public function updateAction(Request $request, Sheet $sheet, Transaction $transaction)
    {
        return $this->render('VimeetAppBundle:Admin/Transaction:update.html.twig', [

        ]);
    }
}
