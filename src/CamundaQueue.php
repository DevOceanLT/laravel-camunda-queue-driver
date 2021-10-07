<?php

namespace DevOceanLT\CamundaQueue;

use Exception;
use Illuminate\Queue\Queue;
use DevOceanLT\Camunda\Http\ExecutionClient;
use DevOceanLT\Camunda\Http\ExternalTaskClient;
use Illuminate\Contracts\Queue\Queue as QueueContract;

class CamundaQueue extends Queue implements QueueContract
{
    /**
     * Get the size of the queue.
     *
     * @param  string|null  $queue
     * @return int
     */
    public function size($queue = null)
    {
        throw new Exception('Function not implemented yet');
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string|object  $job
     * @param  mixed  $data
     * @param  string|null  $queue
     * @return mixed
     */
    public function push($job, $data = '', $queue = null)
    {
        throw new NotSupportedException;
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string  $queue
     * @param  string|object  $job
     * @param  mixed  $data
     * @return mixed
     */
    public function pushOn($queue, $job, $data = '')
    {
        throw new NotSupportedException;
    }

    /**
     * Push a raw payload onto the queue.
     *
     * @param  string  $payload
     * @param  string|null  $queue
     * @param  array  $options
     * @return mixed
     */
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        throw new NotSupportedException;
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  string|object  $job
     * @param  mixed  $data
     * @param  string|null  $queue
     * @return mixed
     */
    public function later($delay, $job, $data = '', $queue = null)
    {
        throw new NotSupportedException;
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  string  $queue
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  string|object  $job
     * @param  mixed  $data
     * @return mixed
     */
    public function laterOn($queue, $delay, $job, $data = '')
    {
        throw new NotSupportedException;
    }

    /**
     * Push an array of jobs onto the queue.
     *
     * @param  array  $jobs
     * @param  mixed  $data
     * @param  string|null  $queue
     * @return mixed
     */
    public function bulk($jobs, $data = '', $queue = null)
    {
        throw new NotSupportedException;
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param  string|null  $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        $job = $this->retrieveNextJob($queue);

        if ($job) {
            return new CamundaJob(
                $this->container, $this, $job, $this->connectionName, $queue
            );
        }
    }

    /**
     * Get the queue or return the default.
     *
     * @param  string|null  $queue
     * @return string
     */
    protected function getQueue($queue)
    {
        return $queue ?: $this->default;
    }

    /**
     * Retrieve the next job from the queue.
     *
     * @param  string  $queue
     * @return array|null
     */
    protected function retrieveNextJob($queue)
    {
        $parameters = [
            'workerId' => "{$queue}QueueWorker",
            'maxTasks' => 1,
            'usePriority' => true,
            'topics' => [
                [
                    'topicName' => $queue,
                    'lockDuration' => 1
                ]
            ]
        ];

        $jobs = ExternalTaskClient::fetchAndLock($parameters);

        if (count($jobs) > 0) {
            $job = $jobs[0];
            $job->variables = ExecutionClient::getLocalVariables($job->executionId);

            return $job;
        }
    }
}
