<?php

namespace MGDigital\BusQue;

final class Implementation
{

    private $queueResolver;
    private $commandSerializer;
    private $commandIdGenerator;
    private $queueDriver;
    private $schedulerDriver;
    private $clock;
    private $commandBusAdapter;

    public function __construct(
        QueueResolverInterface $queueResolver,
        CommandSerializerInterface $commandSerializer,
        CommandIdGeneratorInterface $commandIdGenerator,
        QueueDriverInterface $queueDriver,
        SchedulerDriverInterface $schedulerDriver,
        ClockInterface $clock,
        CommandBusAdapterInterface $commandBusAdapter
    ) {
        $this->queueResolver = $queueResolver;
        $this->commandSerializer = $commandSerializer;
        $this->commandIdGenerator = $commandIdGenerator;
        $this->queueDriver = $queueDriver;
        $this->schedulerDriver = $schedulerDriver;
        $this->clock = $clock;
        $this->commandBusAdapter = $commandBusAdapter;
    }

    public function getQueueResolver(): QueueResolverInterface
    {
        return $this->queueResolver;
    }

    public function getCommandSerializer(): CommandSerializerInterface
    {
        return $this->commandSerializer;
    }

    public function getCommandIdGenerator(): CommandIdGeneratorInterface
    {
        return $this->commandIdGenerator;
    }

    public function getQueueDriver(): QueueDriverInterface
    {
        return $this->queueDriver;
    }

    public function getSchedulerDriver(): SchedulerDriverInterface
    {
        return $this->schedulerDriver;
    }

    public function getClock(): ClockInterface
    {
        return $this->clock;
    }

    public function getCommandBusAdapter(): CommandBusAdapterInterface
    {
        return $this->commandBusAdapter;
    }
}
