<?php

namespace App\Util;

use InvalidArgumentException;

class Config
{
    private array $config = [];

    public function __construct(string $directory)
    {
        $this->scanConfigDirectory($directory);
    }

    private function scanConfigDirectory(string $directory): void
    {
        $files = scandir($directory);

        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $filename = $directory . '/' . $file;
                $this->config += require_once $filename;
            }
        }
    }

    /**
     * Get config option
     * @param string $option
     * @return string
     * @throws InvalidArgumentException
     */
    public function get(string $option): string
    {
        if (!array_key_exists($option, $this->config))
            throw new InvalidArgumentException('Error: no such option in config');

        return $this->config[$option];
    }
}