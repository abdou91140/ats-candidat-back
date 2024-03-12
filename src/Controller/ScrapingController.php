<?php

namespace App\Controller;

use App\Service\CompanyScraper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class ScrapingController extends AbstractController
{
    #[Route('/scraping', name: 'app_scraping')]
    public function scrapeCompanies(CompanyScraper $companyScraper)
    {
        $url = 'https://www.challenges.fr/classements/start-up/2023/';
        $companyScraper->scrapeCompaniesData($url);

        return new Response('Scraping completed.');
    }
}
