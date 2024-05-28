<?php

namespace DevOceanLT\CamundaQueue;

use Illuminate\Queue\Failed\DatabaseFailedJobProvider;
use Illuminate\Queue\Failed\DatabaseUuidFailedJobProvider;
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

        if ($this->app['queue.failer'] instanceof DatabaseFailedJobProvider) {
            $this->app->singleton('queue.failer', function ($app) {
                $config = $app['config']['queue.failed'];
                return new CamundaFailedJobProvider($app['db'], $config['database'], $config['table']);
            });
        }

        if ($this->app['queue.failer'] instanceof DatabaseUuidFailedJobProvider) {
            $this->app->singleton('queue.failer', function ($app) {
                $config = $app['config']['queue.failed'];
                return new CamundaUuidFailedJobProvider($app['db'], $config['database'], $config['table']);
            });
        }
    }
}