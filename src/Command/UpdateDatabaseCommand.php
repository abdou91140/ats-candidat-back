<?php

namespace App\Command;

use App\Service\CompanyScraper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-database',
    description: 'Ajout des données en base via le scraping du site challenges.fr',
)]
class UpdateDatabaseCommand extends Command
{
    private $companyScraper;
    private $url;
    public function __construct(CompanyScraper $companyScraper, string $url)
    {
        parent::__construct();
        $this->companyScraper = $companyScraper;
        $this->url = $url;
    }

    protected function configure(): void
    {
        $this->setDescription('Update the database with data from challenges.fr');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $output->writeln('Updating the database with data from challenges.fr...');

        // Specify the URL to scrape
        $scrapingUrl = '';
        $years = ['2023', '2022', '2021'];

        foreach ($years as $year) {
            $scrapingUrl =  $this->url . $year;

            $this->companyScraper->scrapeCompaniesData($scrapingUrl);
        }
        $io->success('Database updated successfully.');

        return Command::SUCCESS;
    }
}
