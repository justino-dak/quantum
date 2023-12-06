<?php
namespace App\Service;

use App\Repository\Newsletter\UserRepository;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class MessengerService 
{
    /** @var KernelInterface */
    private $kernel;

    /** @var UserRepository */
    private $userRepository;

    public function __construct (
        KernelInterface $kernel, 
        UserRepository $userRepository
    )
    {
        $this->kernel = $kernel;
        $this->userRepository = $userRepository;
    }

    public function async() 
    {
        $application= new Application($this->kernel);
        $application->setAutoExit(true);

        $input= new ArrayInput([
            'command'=> 'messenger:consume',
            'async',
            // '-l'=>count($this->userRepository->findBy(['is_valid'=>true])),
            // '-f'=>5,
            // '-t'=>10,
            // '-m'=>'512M'
        ]);

        $output= new BufferedOutput();

        if(! defined('STDIN')) define('STDIN', fopen("php://stdin","r"));
        $application->run($input,  $output);


        $content=$output->fetch();

        return $content;

    }

    public function stop()
    {
        $application= new Application($this->kernel);
        $application->setAutoExit(true);

        $input= new ArrayInput([
            'command'=> 'messenger:stop-workers',
        ]);

        $output= new BufferedOutput();

        if(! defined('STDIN')) define('STDIN', fopen("php://stdin","r"));
        $application->run($input, $output);


        $content=$output->fetch();

        return $content;

    }

    public function failed()
    {
        $application= new Application($this->kernel);
        $application->setAutoExit(true);

        $input= new ArrayInput([
            'command'=> 'messenger:consume',
            'failed',
            // '-l'=>count($this->userRepository->findBy(['is_valid'=>true])),
            // '-f'=>5,
            // '-t'=>10,
            // '-m'=>'512M'
        ]);

        $output= new BufferedOutput();

        if(! defined('STDIN')) define('STDIN', fopen("php://stdin","r"));
        $application->run($input, $output);


        $content=$output->fetch();

        return $content;

    }


}