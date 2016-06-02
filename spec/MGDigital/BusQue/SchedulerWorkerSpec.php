<?php

namespace spec\MGDigital\BusQue;

use MGDigital\BusQue\ReceivedScheduledCommand;
use MGDigital\BusQue\SchedulerWorker;

final class SchedulerWorkerSpec extends AbstractSpec
{

    public function it_is_initializable()
    {
        $this->shouldHaveType(SchedulerWorker::class);
    }
    
    public function it_can_receive_and_queue_a_scheduled_command()
    {
        $dateTime = new \DateTime();
        $scheduledCommand = new ReceivedScheduledCommand('test_queue', 'test_id', 'serialized', $dateTime);
        $this->schedulerAdapter->awaitScheduledCommand($this->clock, null)->willReturn($scheduledCommand);
        $this->queueAdapter->queueCommand('test_queue', 'test_id', 'serialized')->shouldBeCalled();
        $this->work(1);
    }

}