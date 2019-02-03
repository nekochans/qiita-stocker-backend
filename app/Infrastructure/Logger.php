<?php

namespace App\Infrastructure;

use Ramsey\Uuid\Uuid;
use Nekonomokochan\PhpJsonLogger\LoggerBuilder;
use Nekonomokochan\PhpJsonLogger\SlackHandlerBuilder;
use Nekonomokochan\PhpJsonLogger\Logger as JsonLogger;

class Logger
{
    /**
     * @param array $config
     * @return JsonLogger
     * @throws \Monolog\Handler\MissingExtensionException
     */
    public function __invoke(array $config)
    {
        $slackHandlerBuilder = new SlackHandlerBuilder($config['slack_token'], $config['slack_channel']);

        $traceId = Uuid::uuid4();
        $builder = new LoggerBuilder($traceId);
        $builder->setChannel('qiita-stocker-backend');
        $builder->setLogLevel(JsonLogger::toMonologLevel($config['level']));
        $builder->setFileName(storage_path('logs/qiita-stocker-backend-' . php_sapi_name() . '.log'));
        $builder->setMaxFiles($config['days']);
        $builder->setSkipClassesPartials(['Illuminate\\']);
        $builder->setSlackHandler($slackHandlerBuilder->build());

        return $builder->build();
    }
}
