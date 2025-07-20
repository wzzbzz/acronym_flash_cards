<?php

namespace App\Command;

use App\Repository\AcronymRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

#[AsCommand(
    name: 'app:fetch-og-meta',
    description: 'Fetch Open Graph metadata for Acronyms with Wikipedia URLs',
)]
class FetchOgMetaCommand extends Command
{
    private AcronymRepository $acronymRepository;
    private EntityManagerInterface $em;

    public function __construct(AcronymRepository $acronymRepository, EntityManagerInterface $em)
    {
        parent::__construct();
        $this->acronymRepository = $acronymRepository;
        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $client = HttpClient::create();

        $all = $this->acronymRepository->findAll();
        $acronyms = array_filter(
            $all,
            fn($a) =>
            $a->getWikipediaUrl()
        );

        foreach ($acronyms as $acronym) {
            $url = $acronym->getWikipediaUrl();

            if (!$url) {
                continue;
            }

            $io->text("Fetching OG metadata for: {$url}");

            try {
                $response = $client->request('GET', $url);
                $html = $response->getContent();
                $crawler = new Crawler($html);

                // Title fallback: <meta property="og:title"> OR <title>
                $ogTitle = null;
                try {
                    $ogTitle = $crawler->filterXPath('//meta[@property="og:title"]')->attr('content');
                } catch (\Throwable) {
                    try {
                        $ogTitle = $crawler->filter('title')->text();
                    } catch (\Throwable) {
                    }
                }

                // Description fallback: <meta> OR first paragraph
                $ogDesc = null;
                try {
                    $ogDesc = $crawler->filterXPath('//meta[@property="og:description"]')->attr('content');
                } catch (\Throwable) {
                    try {
                        $ogDesc = $crawler->filterXPath('//meta[@name="description"]')->attr('content');
                    } catch (\Throwable) {
                        try {
                            // Use the first visible paragraph in the article content
                            $rawParagraph = $crawler->filter('#mw-content-text .mw-parser-output > p')->reduce(function (Crawler $node) {
                                return trim($node->text()) !== '';
                            })->first()->text();

                            // Clean the paragraph
                            $cleaned = preg_replace('/\[[^\]]*\]/', '', $rawParagraph); // remove [1], [citation needed]
                            $cleaned = strip_tags($cleaned);                           // strip HTML tags
                            $cleaned = html_entity_decode($cleaned);                   // decode &amp;, etc.
                            $ogDesc = trim(preg_replace('/\s+/', ' ', $cleaned));      // normalize whitespace

                        } catch (\Throwable) {
                            // Still nothing
                        }
                    }
                }

                // truncate the description if it's too long;  the field is 255 characters
                if ($ogDesc && mb_strlen($ogDesc) > 255) {
                    $ogDesc = mb_substr($ogDesc, 0, 252) . '...';
                }

                // Image fallback: first image in infobox
                $ogImage = null;
                try {
                    $ogImage = $crawler->filter('.infobox img')->first()->attr('src');
                    if ($ogImage && str_starts_with($ogImage, '//')) {
                        $ogImage = 'https:' . $ogImage;
                    }
                } catch (\Throwable) {
                    // No image found
                }

                $acronym->setOgTitle($ogTitle);
                $acronym->setOgDescription($ogDesc);
                $acronym->setOgImageUrl($ogImage);


                $this->em->persist($acronym);
                $io->success("✓ Updated metadata for: {$acronym->getCode()}");
            } catch (\Throwable $e) {
                $io->warning("⚠ Could not fetch OG data for: {$url} — " . $e->getMessage());
            }
        }

        $this->em->flush();
        $io->success('Finished updating Open Graph metadata.');

        return Command::SUCCESS;
    }
}
