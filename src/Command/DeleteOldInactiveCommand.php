<?php

namespace App\Command;

use App\Repository\CostumerRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:delete-old-inactive',
    description: 'Deletes Costumers that are atleast 6 months old',
)]
class DeleteOldInactiveCommand extends Command
{
    public function __construct(
        private CostumerRepository $costumerRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run; Run without executing deletion');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('dry-run')) {
            $io->note('Dry mode enabled');

            $count = $this->costumerRepository->countOldInactive();
        } else {
            $count = $this->costumerRepository->deleteOldInactive();
        }

        $io->success($count ? sprintf('Deleted %d old Costumer(s).', $count) : 'No Costumers to delete');

        return Command::SUCCESS;
    }
}
