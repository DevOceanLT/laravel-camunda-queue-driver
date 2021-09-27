<?php

namespace DevOceanLT\CamundaQueue;

use Illuminate\Queue\QueueManager;
use Illuminate\Support\ServiceProvider;
use DevOceanLT\CamundaQueue\CamundaConnector;

class CamundaServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/camunda.php',
            'queue.connections.camunda'
        );
    }

    /**
     * Register the application's event listeners.
     *
     * @return void
     */
    public function boot(): void
    {
        /** @var QueueManager $queue */
        $queue = $this->app['queue'];

        $queue->addConnector('camunda', function () {
            return new CamundaConnector;
        });
    }
}