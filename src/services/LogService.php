<?php

namespace unionco\meilisearch\services;

use Craft;
use craft\base\Component;

class LogService extends Component
{
    public const LVL_DEBUG = 0;
    public const LVL_INFO = 1;
    public const LVL_WARN = 2;
    public const LVL_ERROR = 3;

    protected static $filePath = '';
    /** @var false|resource */
    protected static $fileHandle = false;

    public static function debug(string $key, $message): void
    {
        static::log($key, $message, self::LVL_DEBUG);
    }

    public static function info(string $key, $message): void
    {
        static::log($key, $message, self::LVL_INFO);
    }

    public static function warn(string $key, $message): void
    {
        static::log($key, $message, self::LVL_WARN);
    }

    public static function error(string $key, $message): void
    {
        static::log($key, $message, self::LVL_ERROR);
    }

    protected static function log(string $key, $message, int $level = self::LVL_INFO): void
    {
        if (CRAFT_ENVIRONMENT === 'production' && $level < self::LVL_WARN) {
            return;
        }
        static::openFile();
        $content = (\is_string($message) || \is_numeric($message))
        ? $message
        : print_r($message, true);
        $str = date('Y-m-d H:i:s') . "\t[$key]\t$content\n";
        fwrite(static::$fileHandle, $str);
        static::closeFile();
    }

    protected static function openFile()
    {
        if (!static::$filePath) {
            static::$filePath = Craft::$app->getPath()->getLogPath() . '/meili.log';
        }
        static::$fileHandle = fopen(static::$filePath, 'a');
    }

    protected static function closeFile()
    {
        if (static::$fileHandle) {
            fclose(static::$fileHandle);
        }
    }
}
