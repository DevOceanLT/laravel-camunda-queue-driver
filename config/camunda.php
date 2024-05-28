<?php

/**
 * This is an example of queue connection configuration.
 * It will be merged into config/queue.php.
 * You need to set proper values in `.env`.
 */
return [

    'driver' => 'camunda',
    'connection' => 'default',
    'queue' => env('CAMUNDA_QUEUE', 'default'),
    'timeout' => env('CAMUNDA_TIMEOUT', 60),
    'jobLocation' => 'App\Jobs\Bpmn',
    'topicToJobMap' => [
        'defaultTopic' => 'App\Jobs\DefaultTopicJob'
    ]

];