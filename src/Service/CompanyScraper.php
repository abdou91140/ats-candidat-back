<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\Contact;
use App\Entity\Type;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;


class CompanyScraper 
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
            if ($url != null || $url != '') {
                $browser->request('GET', $url);
            } else {
                return;
            }
            $companyLinks = $this->extractCompanyLinks($browser->getCrawler());
            $this->fetchCompanyData($companyLinks);
        } catch (\Throwable $e) {
            //            echo 'Error: ' . $e->getMessage();
            return;
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
        $companyMail = $crawler->filter(".contact-email > a:nth-child(1) > span:nth-child(2)")->text();
        $companySector = $crawler->filter(".header-info > .sector")->text();
        $responseData = ['name' => $companyName, 'description' => $companyDescription, 'date' => $companyDate,  'size' => $companySize, 'members' => $companyMembers, 'url' => $companyWebsite, 'mail' => $companyMail ? $companyMail : null, 'sector' => $companySector];
        return $responseData;
    }
    private function persistCompanyToDatabase(array $companyData)
    {
        $existingCompany = $this->entityManager->getRepository(Company::class)->findOneBy([
            'name' => $companyData['name'],
        ]);
        if ($existingCompany !== null) {
            return;
        }
        $companyEntity = new Company();
        $companyEntity->setName(strtoupper($companyData['name']));
        $companyEntity->setDescription($companyData['description']);
        $companyEntity->setDate($companyData['date']);
        $companyEntity->setSize($companyData['size']);
        $contact = $this->extractNames($companyData['members']);
        $newContact = new Contact();
        $newContact->setFullname($contact);
        $newContact->setMail($companyData['mail']);
        $companyEntity->setMembers($companyData['members']);
        $companyEntity->setUrl($companyData['url']);
        $type = new Type();
        $newType = in_array($companyData['sector'], $type::LIST) ? $companyData['sector'] : null;
        $companyEntity->setType($type->setName($newType));
        $companyEntity->addContact($newContact);
        $this->entityManager->persist($newContact);
        $this->entityManager->persist($companyEntity);
        $this->entityManager->flush();
    }
    function extractNames($string)
    {
        $people = "";

        // Split the input string by newline character
        $lines = explode("\n", $string);

        foreach ($lines as $line) {
            // Extract name and remove any titles or age
            $name = trim(preg_replace('/[^\p{L}\s]|(?<=\b)(CEO|CTO)\b|\d+/u', '', $line));
            // Split the name into first name and last name
            $nameParts = explode(' ', $name);
            // Extract first name and last name
            $firstName = $nameParts[0];
            $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

            // Add to the people array
            $people = $firstName . "-" . $lastName;
        }

        return $people;
    }
}
