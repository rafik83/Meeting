<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller;

use Proximum\Vimeet\Domain\Model\Nomenclature;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class NomenclatureController extends Controller
{
    public function listAction()
    {
        $nomenclatures = $this->get('repository.nomenclature_repository')->getAll();

        return $this->render('AdminBundle:Nomenclature:list.html.twig', [
            'nomenclatures' => $nomenclatures,
        ]);
    }

    public function readAction(Nomenclature $nomenclature)
    {
        return $this->render('AdminBundle:Nomenclature:read.html.twig', [
            'nomenclature' => $nomenclature,
        ]);
    }
}
