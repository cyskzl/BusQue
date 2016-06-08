<?php

namespace spec\MGDigital\BusQue;

use MGDigital\BusQue\QueueWorker;
use MGDigital\BusQue\ReceivedCommand;

final class QueueWorkerSpec extends AbstractSpec
{

    public function it_is_initializable()
    {
        $this->shouldHaveType(QueueWorker::class);
    }

    public function it_can_receive_and_handle_a_queued_command()
    {
        $receivedCommand = new ReceivedCommand('test_queue', 'test_id', 'serialized');
        $this->queueAdapter->awaitCommand('test_queue', null)->willReturn($receivedCommand);
        $this->commandSerializer->unserialize('serialized')->willReturn('test_command');
        $this->commandBusAdapter->handle('test_command', true)->shouldBeCalled();
        $this->queueAdapter->setCommandCompleted('test_queue', 'test_id')->shouldBeCalled();
        $this->work('test_queue', 1);
    }

    public function it_can_handle_a_command_error()
    {
        $exception = new \Exception;
        $errorReceivedCommand = new ReceivedCommand('test_queue', 'error_id', 'error_serialized');
        $nextReceivedCommand = new ReceivedCommand('test_queue', 'next_id', 'serialized');
        $this->queueAdapter->awaitCommand('test_queue', null)->willReturn($errorReceivedCommand, $nextReceivedCommand);
        $this->commandSerializer->unserialize('error_serialized')->willReturn('error_command');
        $this->commandSerializer->unserialize('serialized')->willReturn('next_command');
        $this->commandBusAdapter->handle('error_command', true)->willThrow($exception);
        $this->commandBusAdapter->handle('next_command', true)->willReturn(null);
        $this->queueAdapter->setCommandFailed('test_queue', 'error_id')->shouldBeCalled();
        $this->errorHandler->handleCommandError('error_command', $exception)->shouldBeCalled();
        $this->commandBusAdapter->handle('next_command', true)->shouldBeCalled();
        $this->queueAdapter->setCommandCompleted('test_queue', 'next_id')->shouldBeCalled();
        $this->work('test_queue', 2);
    }

    public function it_can_handle_an_unserialization_error()
    {
        $exception = new \Exception;
        $errorReceivedCommand = new ReceivedCommand('test_queue', 'error_id', 'cant_be_unserialized');
        $nextReceivedCommand = new ReceivedCommand('test_queue', 'next_id', 'serialized');
        $this->queueAdapter->awaitCommand('test_queue', null)->willReturn($errorReceivedCommand, $nextReceivedCommand);
        $this->commandSerializer->unserialize('cant_be_unserialized')->willThrow($exception);
        $this->commandSerializer->unserialize('serialized')->willReturn('next_command');
        $this->commandBusAdapter->handle('next_command', true)->willReturn(null);
        $this->queueAdapter->setCommandFailed('test_queue', 'error_id')->shouldBeCalled();
        $this->errorHandler->handleUnserializationError('test_queue', 'error_id', 'cant_be_unserialized', $exception)->shouldBeCalled();
        $this->commandBusAdapter->handle('next_command', true)->shouldBeCalled();
        $this->queueAdapter->setCommandCompleted('test_queue', 'next_id')->shouldBeCalled();
        $this->work('test_queue', 2);
        
    }

}