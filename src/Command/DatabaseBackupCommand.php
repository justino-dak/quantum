<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Process\Process;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Goodby\CSV\Export\Protocol\Exception\IOException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;

#[AsCommand(
    name: 'app:database-backup',
    description: 'Générer un backup de la base de données dans son état actuel',
)]
class DatabaseBackupCommand extends Command
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;
    /**
     * @var string
     */

     private $projectDirectory;

    public function __construct(ManagerRegistry $managerRegistry, string $projectDirectory)
    {
        parent::__construct();
        $this->managerRegistry = $managerRegistry;
        $this->projectDirectory = $projectDirectory;

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileSystem= new Filesystem();
        $backupDirectory="{$this->projectDirectory}/var/backup";
        $io = new SymfonyStyle($input, $output);

        if ($fileSystem->exists($backupDirectory)=== true) {
            $fileSystem->remove($backupDirectory);
        }
         try {
            $fileSystem->mkdir($backupDirectory,0700);
         } catch (IOException $error) {
            throw new IOException($error);
         }

         /** @var Connection $databaseConnection */
        $databaseConnection = $this->managerRegistry->getConnection();

        [
            'host'=>$databaseHost,
            'port'=>$databasePort,
            'user'=>$databaseUser,
            'password'=>$databasePassword,
            'dbname'=>$databaseName,
        ]=$databaseConnection->getParams();

        $filePathTarget="--result-file={$backupDirectory}/backup.sql";

        $command=[
            'mysqldump',
            '--host',
            $databaseHost,
            '--port',
            $databasePort,
            '--user',
            $databaseUser,
            '-p'.$databasePassword,
            $databaseName,
            $filePathTarget
        ];

        if ($databasePassword ==='') {
            $command=[
                'mysqldump',
                '--host',
                $databaseHost,
                '--port',
                $databasePort,
                '--user',
                $databaseUser,
                $databaseName,
                $filePathTarget
            ];        
        }

        $process=new Process($command);
        $process->setTimeout(60);

        $process->run();

        if ($process->isSuccessful() ===  false) {
            throw new ProcessFailedException($process);
        }

        $io->success('Backup file generated !');

        return Command::SUCCESS;
    }
}
