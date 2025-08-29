<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response; // Ajout de l'import

class DefaultController extends AbstractController
{
    
    #[Route('/', name: 'home')]
    public function indexAction(): Response
    {
        return $this->render('default/index.html.twig');
    }
}
