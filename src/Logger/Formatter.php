<?php

namespace Library\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\LogRecord;

class Formatter extends LineFormatter
{
    /**
     * @inheritdoc
     */
    public function format(LogRecord $record): string
    {
        $vars = $this->normalizeRecord($record);

        $content = "[{$vars['datetime']}] {$vars['channel']}.{$vars['level_name']}";

        if (isset($record->context['_trace_']['point'])) {
            $point = json_decode($record->context['_trace_']['point'], true);
            $point = $point[0];
            $content .= " {$point['file']}({$point['line']})\n";
        }
        unset($vars['context']['_trace_'], $vars['context']['addition']);

        if (isset($record->context['exception'])) {
            $exception = $record->context['exception'];
            $content .= "message:\n {$exception['message']}\n";
            $content .= "point:\n {$exception['class']} - {$exception['file']}({$exception['line']})\n";
            $content .= "stack:\n";
            foreach ($exception['stack'] as $line) {
                $content .= " {$line}\n";
            }
            unset($vars['context']['exception']);
        } else {
            $content .= "message: {$vars['message']}\n";
        }

        if ($vars['context']) {
            $content .= "context: " . json_encode($vars['context'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
        }

        if (isset($record->context['_trace_']['point'])) {
            $content .= "point: " . $record->context['_trace_']['point'] . PHP_EOL;
        }

        $content .= "\n";

        return $content;
    }
}
