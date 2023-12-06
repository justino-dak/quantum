<?php
namespace App\EventSubscriber;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;


class CronRunningEvent implements EventSubscriberInterface
{
    public function onWorkerRunning(WorkerRunningEvent $event): void
    {

        //cron job commande : // * * * * * bin/console messenger:consume async --memory-limit=128M

        if ($event->isWorkerIdle()) {
            $event->getWorker()->stop();
        }
    }

    /**
     * @return array<string>
     */
    public static function getSubscribedEvents()
    {
        return [
            WorkerRunningEvent::class => 'onWorkerRunning',
        ];
    }
}
