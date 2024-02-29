<?php

namespace App\Command;

use DateTime;
use ZipArchive;
use Doctrine\DBAL\Connection;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use App\Repository\BackupRepository;
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
    name: 'app:backup',
    description: 'Générer un backup de la base de données et du dossier "/public/uploads" dans leurs étast actuels',
)]
class BackupCommand extends Command
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;
    /**
     * @var string
     */
     private $projectDirectory;
    /**
     * @var BackupRepository
     */
    private $backupRepository;

    public function __construct(
        ManagerRegistry $managerRegistry, 
        string $projectDirectory,
        BackupRepository $backupRepository
        )
    {
        parent::__construct();
        $this->managerRegistry = $managerRegistry;
        $this->projectDirectory = $projectDirectory;
        $this->backupRepository = $backupRepository;

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fileSystem= new Filesystem();
        $backupBaseDirectory="{$this->projectDirectory}/var/backups";
        $uploadsDirectory="{$this->projectDirectory}/public/uploads";
        $io = new SymfonyStyle($input, $output);

        $creatAt=new DateTime();
        $backupNamedDirectory="{$backupBaseDirectory}/backup-{$creatAt->format('Y-M-d-H-i-s')}";
        $backupZipPath="{$backupBaseDirectory}/backup-{$creatAt->format('Y-M-d-H-i-s')}.zip";
        $uploadsNamedDirectory="{$backupNamedDirectory}/uploads-{$creatAt->format('Y-M-d-H-i-s')}";

        if ($fileSystem->exists($backupNamedDirectory)=== false) {
            try {
                $fileSystem->mkdir($backupNamedDirectory,0700);
             } catch (IOException $error) {
                throw new IOException($error);
             }    
        }

        $backup= $this->backupRepository->create();
        $backup->setCreatedAt($creatAt);
        $backup->setName("backup-{$creatAt->format('Y-M-d-H-i-s')}");
        $backup->setDownload(0);


        /** @var Connection $databaseConnection */
        $databaseConnection = $this->managerRegistry->getConnection();

        [
            'host'=>$databaseHost,
            'port'=>$databasePort,
            'user'=>$databaseUser,
            'password'=>$databasePassword,
            'dbname'=>$databaseName,
        ]=$databaseConnection->getParams();

        $filePathTarget="--result-file={$backupNamedDirectory}/db-{$creatAt->format('Y-M-d-H-i-s')}.sql";

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

        try {
            $fileSystem->mirror ($uploadsDirectory,$uploadsNamedDirectory);
        } catch (\Throwable $th) {
            throw $th;
        }  
        // archiver le backup en ficher zip
        try {
            // Create a new ZipArchive object
            $zip = new ZipArchive();

            // Open the zip file for writing
            if ($zip->open($backupZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                
                // Create recursive directory iterator
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($backupNamedDirectory),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach ($files as $name => $file) {
                    // Skip directories (they would be added automatically)
                    if (!$file->isDir()) {
                        // Get real and relative path for current file
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($backupZipPath) + 1);

                        // Add current file to archive
                        $zip->addFile($filePath, $relativePath);
                    }
                }     
                
                // Close and save the archive
                $zip->close(); 
                $backup->setLink($backupZipPath);
                $this->backupRepository->save($backup);
                $fileSystem->remove($backupNamedDirectory);

                $io->success('zip file succefully added !');

            } 
        }catch (\Throwable $th) {
            throw $th;
        }


        $io->success('Backup succefully ended !');

        return Command::SUCCESS;
    }
}
