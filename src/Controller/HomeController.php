<?php

namespace App\Controller;

use App\Form\CompanyAutocompleteType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $form = $this->createForm(CompanyAutocompleteType::class);
        return $this->render('home/index.html.twig', [
            'user' => $this->getUser(),
            'companyForm' => $form->createView(),

        ]);
    }
}
