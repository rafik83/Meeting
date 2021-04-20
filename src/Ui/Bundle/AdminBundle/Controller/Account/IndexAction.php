<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Account;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class IndexAction
{
    /** @var Environment */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function __invoke(): Response
    {
        return new Response($this->twig->render('AdminBundle:Account:index.html.twig'));
    }
}
