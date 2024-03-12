<?php
namespace App\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Panther\PantherTestCaseTrait;
use App\Service\CompanyScraper;

class CompanyScraperTest extends KernelTestCase
{
    use PantherTestCaseTrait;

    public function testScrapeCompanies()
    {
        // Replace 'App\Kernel' with the fully-qualified class name of your kernel
//        self::bootKernel(['environment' => 'test']);

        //$client = self::createPantherClient();
        $url = 'https://www.challenges.fr/classements/start-up/';

        $companyScraper = new CompanyScraper();
        $companyScraper->scrapeCompaniesData($url);

        // Add assertions as needed
        // $this->assertEquals(...);
    }
}
