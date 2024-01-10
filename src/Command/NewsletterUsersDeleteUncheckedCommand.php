<?php

namespace App\Command;

use App\Message\NewsletterUserMessage;
use App\Service\NewsletterUserManagerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewsletterUsersDeleteUncheckedCommand extends Command
{
    /**
     * @var NewsletterUserManagerService
     */
    private $userManager;
    /** 
     * @var MessageBusInterface 
     */
    private $messageBus;    

    protected static $defaultName = 'newsletter:users:delete-unchecked';
    protected static $defaultDescription = 'This commande helps to delete uncomfirmed users from newsletter';

    public function __construct(
        NewsletterUserManagerService $userManager,
        MessageBusInterface $messageBus
        )
    {
        // best practices recommend to call the parent constructor first and
        // then set your own properties. That wouldn't work in this case
        // because configure() needs the properties set in this constructor
        $this->userManager = $userManager;
        $this->messageBus = $messageBus;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_OPTIONAL, 'Verbeuse')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $users=$this->userManager->getUnchekedUsers();

        if (count($users)) {
            foreach ($users as $user) {
                $this->messageBus->dispatch( new NewsletterUserMessage($user->getId()));
            }
            
            $io->success(sprintf('%d users are successfully sent to messenger for delection',count($users)));

        }else {
            $io->note('No user found');
        }

        return Command::SUCCESS;
    }
}
