<?php

namespace App\Service;

use App\Entity\Company;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use IntlDateFormatter;

class CompanyScraper extends KernelTestCase
{
    private $url;
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager, string $url)
    {
        $this->url = $url;
        $this->entityManager = $entityManager;
    }
    private function getHttpClient(): HttpClientInterface
    {
        return HttpClient::create();
    }

    private function getHttpBrowser(): HttpBrowser
    {
        return new HttpBrowser($this->getHttpClient());
    }

    public function scrapeCompaniesData(string $url)
    {

        try {
            $browser = $this->getHttpBrowser();
            $browser->request('GET', $this->url);
            $browser->request('GET', $url);
            $companyLinks = $this->extractCompanyLinks($browser->getCrawler());
            $this->fetchCompanyData($companyLinks);
        } catch (\Throwable $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    private function extractCompanyLinks(Crawler $crawler)
    {
        $companyLinks = [];
        $crawler->filter('td a')->each(function ($companyLink) use (&$companyLinks) {
            $link = $companyLink->link();
            $companyLinks[] = $link;
        });

        return $companyLinks;
    }

    private function fetchCompanyData(array $companyLinks)
    {

        foreach ($companyLinks as $companyDetailsUrl) {
            try {
                $browser = $this->getHttpBrowser();
                $companyDetailsCrawler = $browser->request('GET', $companyDetailsUrl->getUri());
                $companyList = $this->extractCompanyData($companyDetailsCrawler);
                $this->persistCompanyToDatabase($companyList);
            } catch (\Throwable $e) {
                echo 'Error fetching data: ' . $e->getMessage();
            }
        }

        return $companyList;
    }

    private function extractCompanyData(Crawler $crawler)
    {
        $companyName = $crawler->filter("h1")->text();
        $companyDescription = $crawler->filter("#description > .text")->text();
        $companyDate = $crawler->filter(".creation-date > .value")->text();
        $companySize = $crawler->filter(".employees-no > .value")->text();
        $companyMembers = $crawler->filter(".members")->text();
        $companyWebsite = $crawler->filter(".web > .photo-container > .site-url")->text();
        $responseData = ['name' => $companyName, 'description' => $companyDescription, 'date' => $companyDate,  'size' => $companySize, 'members' => $companyMembers, 'url' => $companyWebsite];
        return $responseData;
    }
    private function persistCompanyToDatabase(array $companyData)
    {
        $existingCompany = $this->entityManager->getRepository(Company::class)->findOneBy([
            'name' => $companyData['name'],
        ]);

        // If the company already exists, skip the insertion
        if ($existingCompany !== null) {
            return;
        }
        $companyEntity = new Company();
        $companyEntity->setName($companyData['name']);
        $companyEntity->setDescription($companyData['description']);
        $companyEntity->setDate($companyData['date']);
        $companyEntity->setSize($companyData['size']);
        $companyEntity->setMembers($companyData['members']);
        $companyEntity->setUrl($companyData['url']);

        $this->entityManager->persist($companyEntity);
        $this->entityManager->flush();
    }
}
