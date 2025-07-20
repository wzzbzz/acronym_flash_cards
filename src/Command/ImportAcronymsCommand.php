<?php
// src/Command/ImportAcronymsCommand.php

namespace App\Command;

use App\Entity\Acronym;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:import-acronyms',
    description: 'Import acronyms from JSON file',
)]
class ImportAcronymsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $jsonPath = __DIR__ . '/../../data/esp32_acronyms_with_links.json';
        if (!file_exists($jsonPath)) {
            $output->writeln('<error>JSON file not found at: ' . $jsonPath . '</error>');
            return Command::FAILURE;
        }

        $data = json_decode(file_get_contents($jsonPath), true);

        foreach ($data as $categoryName => $acronyms) {
            $category = $this->em->getRepository(Category::class)->findOneBy(['name' => $categoryName]);
            if (!$category) {
                $category = new Category();
                $category->setName($categoryName);
                $this->em->persist($category);
            }

            foreach ($acronyms as $code => $info) {
                $existing = $this->em->getRepository(Acronym::class)->findOneBy(['code' => $code]);
                if ($existing) {
                    continue; // Skip duplicates
                }

                $acronym = new Acronym();
                $acronym->setCode($code);
                $acronym->setMeaning($info['meaning']);
                $acronym->setWikipediaUrl($info['wikipedia']);
                $acronym->setCategory($category);
                $this->em->persist($acronym);
            }
        }

        $this->em->flush();
        $output->writeln('<info>Import complete!</info>');
        return Command::SUCCESS;
    }
}
