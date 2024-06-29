<?php

namespace Fabian\CloudflareUnifiIpImport;

use Exception;

class Scheduler
{
    private string $dataPath = ROOT_DIR . '/data/scheduler.json';
    private array $tasks = [];
    private array $callbacks = [];

    public function __construct()
    {
        if (!file_exists($this->dataPath)) {
            try{
                mkdir(dirname($this->dataPath), 0777, true);
            }catch (Exception $e){}
        }
        // create scheduler.json if it doesn't exist
        if (!file_exists($this->dataPath)) {
            file_put_contents($this->dataPath, json_encode([], JSON_PRETTY_PRINT));
        }

        $this->loadTasks();
    }

    public function saveTasks(): void
    {
        file_put_contents($this->dataPath, json_encode($this->tasks, JSON_PRETTY_PRINT));
    }
    public function loadTasks(): void
    {
        $this->tasks = json_decode(file_get_contents($this->dataPath), true);
    }


    public function registerTask(string $taskName, int $cronIntervalS, $callback): void
    {
        // Always register the callback
        $this->callbacks[$taskName] = $callback;

        if(array_key_exists($taskName, $this->tasks)) {
            return;
        }

        $this->tasks[$taskName] = [
            'cronIntervalMs' => $cronIntervalS * 1000,
            'lastRun' => 0,
            'nextRun' => 0,
        ];
        $this->saveTasks();
    }

    /**
     * @throws Exception
     */
    public function shouldRunTask($taskName): bool
    {
        if (!isset($this->tasks[$taskName])) {
            throw new Exception("Task $taskName not found");
        }

        $task = $this->tasks[$taskName];
        $nextRun = $task['nextRun'] ?? 0;
        $currentTime = microtime(true) * 1000;

        if($nextRun === 0) {
            return true;
        }
        if ($currentTime >= $nextRun) {
            return true;
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public function run(): void
    {
        foreach ($this->tasks as $taskName => $task) {
            if ($this->shouldRunTask($taskName)) {
                $this->callbacks[$taskName]();
                $this->tasks[$taskName]['lastRun'] = (microtime(true) * 1000);
                $this->tasks[$taskName]['nextRun'] = (microtime(true) * 1000) + $task['cronIntervalMs'];
                $this->saveTasks();
            }
        }
    }

}