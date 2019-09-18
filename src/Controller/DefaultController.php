<?php

/*
 * This file is part of rimi-itk/mailhogger.
 *
 * (c) 2018–2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->redirectToRoute('easyadmin');
    }
}
