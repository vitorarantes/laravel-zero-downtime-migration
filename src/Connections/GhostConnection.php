<?php

namespace Daursu\ZeroDowntimeMigration\Connections;

use Illuminate\Support\Arr;

class GhostConnection extends BaseConnection
{
    /**
     * Executes the SQL statement through pt-online-schema-change.
     *
     * @param  string $query
     * @param  array  $bindings
     * @return bool|int
     */
    public function statement($query, $bindings = [])
    {
        $table = $this->extractTableFromQuery($query);

        return $this->runProcess(array_merge(
            [
                'gh-ost',
                $this->isPretending() ? '' : '--execute',
            ],
            Arr::get($this->config, 'options', []),
            [
                sprintf('--user="%s"', $this->getConfig('username')),
                sprintf('--password="%s"', $this->getConfig('password')),
                sprintf('--host="%s"', $this->getConfig('host')),
                sprintf('--port="%s"', $this->getConfig('port')),
                sprintf('--database="%s"', $this->getConfig('database')),
                sprintf('--table="%s"', $table),
                sprintf('--alter="%s"', $this->cleanQuery($query)),
            ]
        ));
    }

    /**
     * Hide the username/pw from console output.
     *
     * @param array $command
     * @return string
     */
    protected function maskSensitiveInformation(array $command): string
    {
        return collect($command)->map(function ($config) {
            $config = preg_replace('/(password=.*?),/', 'password=*****,', $config);

            return preg_replace('/(user=.*?),/', 'user=*****,', $config);
        })->implode(' ');
    }
}
