<?php

namespace DevOceanLT\CamundaQueue;

use Illuminate\Queue\Failed\DatabaseFailedJobProvider;

class CamundaFailedJobProvider extends DatabaseFailedJobProvider
{
    /**
     * Log a failed job into storage.
     *
     * @param  string  $connection
     * @param  string  $queue
     * @param  string  $payload
     * @param  \Throwable  $exception
     * @return int|null
     */
    public function log($connection, $queue, $payload, $exception)
    {
        if (config('queue.connections.' . $connection . '.driver') === 'camunda') {
            return null;
        }

        return parent::log($connection, $queue, $payload, $exception);
    }
}
