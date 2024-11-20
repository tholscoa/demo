<?php

namespace App\Command;

use App\Repository\BookRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class ExportBooksCommand extends Command
{
    private $bookRepository;

    public function __construct(BookRepository $bookRepository)
    {
        $this->bookRepository = $bookRepository;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('app:book:export')
            ->setDescription('Export books to a JSON file')
            ->addOption('directory', null, InputOption::VALUE_OPTIONAL, 'Directory to save the file', 'var/export');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $directory = $input->getOption('directory');
        $filesystem = new Filesystem();

        // Ensure the directory exists
        $filesystem->mkdir($directory);

        $books = $this->bookRepository->findAll();
        $data = [];

        foreach ($books as $book) {
            $categories = $book->getCategories()->map(fn($category) => $category->getName())->toArray();
            $activeUsers = $this->getActiveUsers($book);

            $data[] = [
                'id' => $book->getId(),
                'author' => $book->getAuthor(),
                'title' => $book->getTitle(),
                'categories' => $categories,
                'reviews' => count($book->getReviews()),
                'bookmarks' => count($book->getBookmarks()),
                'activeUsers' => $activeUsers,
            ];
        }

        // Generate the JSON file
        $filePath = rtrim($directory, '/') . '/books.json';
        $filesystem->dumpFile($filePath, json_encode($data, JSON_PRETTY_PRINT));

        $output->writeln(sprintf('Books exported successfully to: %s', $filePath));

        return Command::SUCCESS;
    }

    private function getActiveUsers($book): array
    {
        $activeUsers = [];
        $reviews = $book->getReviews();
        $bookmarks = $book->getBookmarks();

        foreach ($reviews as $review) {
            $user = $review->getUser(); // Assuming Review has a getUser method
            if ($user && $bookmarks->exists(fn($key, $bookmark) => $bookmark->getUser() === $user)) {
                $activeUsers[] = $user->getEmail(); // Assuming User has a getEmail method
            }
        }

        return array_unique($activeUsers);
    }
}
