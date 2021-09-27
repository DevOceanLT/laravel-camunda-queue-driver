<?php

namespace DevOceanLT\CamundaQueue;

use Illuminate\Queue\Jobs\Job;
use Illuminate\Container\Container;
use Illuminate\Queue\Events\JobFailed;
use DevOceanLT\CamundaQueue\CamundaQueue;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\ManuallyFailedException;
use DevOceanLT\Camunda\Http\ExternalTaskClient;
use Illuminate\Contracts\Queue\Job as JobContract;

class CamundaJob extends Job implements JobContract
{
    /**
     * The Camunda BPMN queue instance.
     *
     * @var CamundaQueue
     */
    protected $camundaQueue;

    /**
     * The Camunda BPMN job payload.
     *
     * @var \stdClass
     */
    protected $job;

    /**
     * Create a new job instance.
     *
     * @param  \Illuminate\Container\Container  $container
     * @param  CamundaQueue  $camunda
     * @param  \stdClass  $job
     * @param  string  $connectionName
     * @param  string  $queue
     * @return void
     */
    public function __construct(Container $container, CamundaQueue $camundaQueue, $job, $connectionName, $queue)
    {
        $this->job = $job;
        $this->queue = $queue;
        $this->camundaQueue = $camundaQueue;
        $this->container = $container;
        $this->connectionName = $connectionName;

        $this->topicToJobMap = config('queue.connections.camunda-bpmn.topicToJobMap');
    }

    /**
     * Release the job back into the queue.
     *
     * @param  int  $delay
     * @return void
     */
    public function release($delay = 0)
    {
        parent::release($delay);

        ExternalTaskClient::unlock($this->job->id);
    }

    /**
     * Delete the job from the queue.
     *
     * @return void
     */
    public function delete()
    {
        parent::delete();

        $parameters = [
            'workerId' => "{$this->queue}QueueWorker",
        ];

        ExternalTaskClient::complete($this->job->id, $parameters);
    }

    /**
     * Get the number of times the job has been attempted.
     *
     * @return int
     */
    public function attempts()
    {
        // $retries = $this->job->retries ?? 3;
        // return (int) (3 - $retries);
        return 1;
    }

    /**
     * Get the job identifier.
     *
     * @return string
     */
    public function getJobId()
    {
        return $this->job->id;
    }

    /**
     * Get the raw body string for the job.
     *
     * @return string
     */
    public function getRawBody()
    {
        $job = new $this->topicToJobMap[$this->job->topicName];
        $job->queue = $this->job->topicName;
        $job->businessKey = $this->job->businessKey;

        $body = [
            "uuid" => $this->job->id,
            "displayName" => $this->topicToJobMap[$this->job->topicName],
            "job" => "Illuminate\\Queue\\CallQueuedHandler@call",
            "maxTries" => null,
            "maxExceptions" => null,
            "backoff" => null,
            "timeout" => 10,
            "retryUntil" => null,
            "data" => [
                "commandName" => $this->topicToJobMap[$this->job->topicName],
                "command" => serialize($job)
            ]
        ];

        return json_encode($body);
    }

    /**
     * Get the database job record.
     *
     * @return \Illuminate\Queue\Jobs\DatabaseJobRecord
     */
    public function getJobRecord()
    {
        return $this->job;
    }

    /**
     * Get the name of the queued job class.
     *
     * @return string
     */
    public function getName()
    {
        return $this->topicToJobMap[$this->job->topicName];
    }

    /**
     * Delete the job, call the "failed" method, and raise the failed job event.
     *
     * @param  \Throwable|null  $e
     * @return void
     */
    public function fail($e = null)
    {
        $parameters = [
            'workerId' => "{$this->queue}QueueWorker",
        ];

        ExternalTaskClient::failure($this->job->id, $parameters);

        $this->markAsFailed();

        if ($this->isDeleted()) {
            return;
        }

        try {
            // If the job has failed, we will release it, call the "failed" method and then call
            // an event indicating the job has failed so it can be logged if needed. This is
            // to allow every developer to better keep monitor of their failed queue jobs.
            // $this->delete();
            $this->release();

            $this->failed($e);
        } finally {
            $this->resolve(Dispatcher::class)->dispatch(new JobFailed(
                $this->connectionName, $this, $e ?: new ManuallyFailedException
            ));
        }
    }
}