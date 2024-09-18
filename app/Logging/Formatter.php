<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Logger as MonologLogger;

class Formatter
{
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $lineFormatter = new LineFormatter(
                "[%datetime%] %level_name%: %message% %context% %extra%\n",
                'H:i:s',
                true,
                true
            );

            $lineFormatter->includeStacktraces(true);

            $handler->setFormatter($lineFormatter);

            $handler->pushProcessor(new IntrospectionProcessor(
                MonologLogger::DEBUG,
                ['Illuminate\\']
            ));

            $handler->pushProcessor(function ($record) {
                return $record;
            });
        }
    }
}
