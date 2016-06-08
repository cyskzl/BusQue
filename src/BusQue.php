<?php

namespace MGDigital\BusQue;

class BusQue
{

    private $implementation;

    public function __construct(Implementation $implementation)
    {
        $this->implementation = $implementation;
    }

    public function getQueueName($command): string
    {
        return $this->implementation->getQueueResolver()->resolveQueueName($command);
    }

    public function serializeCommand($command): string
    {
        return $this->implementation->getCommandSerializer()->serialize($command);
    }

    public function unserializeCommand(string $serialized)
    {
        return $this->implementation->getCommandSerializer()->unserialize($serialized);
    }

    public function generateCommandId($command): string
    {
        return $this->implementation->getCommandIdGenerator()->generateId($command);
    }

    public function queueCommand($command, string $commandId = null)
    {
        $this->implementation->getCommandBusAdapter()->handle(new QueuedCommand($command, $commandId));
    }

    public function scheduleCommand($command, \DateTime $dateTime, string $commandId = null)
    {
        $this->implementation->getCommandBusAdapter()->handle(new ScheduledCommand($command, $dateTime, $commandId));
    }

    public function getCommandStatus(string $queueName, string $commandId): string
    {
        return $this->implementation->getQueueAdapter()->getCommandStatus($queueName, $commandId);
    }

    public function getQueuedCount(string $queueName): int
    {
        return $this->implementation->getQueueAdapter()->getQueuedCount($queueName);
    }

    public function purgeCommand(string $queueName, string $commandId)
    {
        $this->implementation->getQueueAdapter()->purgeCommand($queueName, $commandId);
    }

    public function clearQueue(string $queueName)
    {
        $this->implementation->getQueueAdapter()->clearQueue($queueName);
    }

    public function deleteQueue(string $queueName)
    {
        $this->implementation->getQueueAdapter()->deleteQueue($queueName);
    }

    public function listQueues(): array
    {
        return $this->implementation->getQueueAdapter()->getQueueNames();
    }

    public function listQueuedIds(string $queueName, int $offset = 0, int $limit = 10): array
    {
        return $this->implementation->getQueueAdapter()->getQueuedIds($queueName, $offset, $limit);
    }

    public function getInProgressCount(string $queueName): int
    {
        return $this->implementation->getQueueAdapter()->getConsumingCount($queueName);
    }

    public function listInProgressIds(string $queueName, int $offset = 0, int $limit = 10): array
    {
        return $this->implementation->getQueueAdapter()->getConsumingIds($queueName, $offset, $limit);
    }

    public function getCommand(string $queueName, string $id)
    {
        $serialized = $this->implementation->getQueueAdapter()->readCommand($queueName, $id);
        return $this->unserializeCommand($serialized);
    }

    public function workQueue(string $queueName, int $n = null, int $time = null)
    {
        (new QueueWorker($this->implementation))->work($queueName, $n, $time);
    }

    public function workSchedule(int $n = null, int $time = null)
    {
        (new SchedulerWorker($this->implementation))->work($n, $time);
    }
}
