<?php

namespace App\Command;

use App\Repository\ReviewRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReviewStatisticsCommand extends Command
{
    private $reviewRepository;

    public function __construct(ReviewRepository $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:review:statistics')
            ->setDescription('Display the day/month with the most reviews')
            ->addOption('month', null, InputOption::VALUE_NONE, 'Display by month');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('month')) {
            $result = $this->reviewRepository->getHighestReviewMonth();
            $output->writeln('Most reviews in: ' . $result);
        } else {
            $result = $this->reviewRepository->getHighestReviewDay();
            $output->writeln('Most reviews on: ' . $result);
        }
        return Command::SUCCESS;
    }
}
