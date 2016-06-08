Feature: Command Bus Queue
  In order to queue and schedule asynchronous commands
  As a developer
  I need a Command Bus Queue

  Scenario: Queuing a command
    Given the queue is empty
    And I queue "test_command"
    Then there should be 1 commands in the queue
    And the command should have a status of "queued"
    When I run the queue worker
    Then the command should have run
    And the command should have a status of "completed"
    And there should be 0 commands in the queue

  Scenario: Queuing commands with identifiers
    Given the queue is empty
    And I queue "test_command" with ID "test_command_id"
    Then "test_command_id" should be in the list of queued IDs
    And I queue "second_test_command" with ID "test_command_id"
    Then there should be 1 commands in the queue
    And the command with ID "test_command_id" should resolve to "second_test_command"
    And I queue "third_test_command" with ID "another_command_id"
    Then there should be 2 commands in the queue
    And I run the queue worker
    And I run the queue worker
    Then there should be 0 commands in the queue
    And the command should have a status of "completed"
    And I queue "test_command" with ID "test_command_id"
    Then there should be 1 commands in the queue
    And the command should have a status of "queued"
    And I run the queue worker
    Then there should be 0 commands in the queue
    And the command should have a status of "completed"

  Scenario: Queuing a command which fails
    Given the queue is empty
    Given I queue "test_command"
    And the command will throw an exception when it is handled
    Then the command should have a status of "queued"
    When I run the queue worker
    Then the command should have a status of "failed"

  Scenario: Cancelling a command
    Given the queue is empty
    Given I queue "test_command" with ID "test_command_id"
    Then there should be 1 commands in the queue
    And I cancel "test_command_id"
    Then there should be 0 commands in the queue
    And the command should have a status of "not_found"

  Scenario: Scheduling commands
    Given the queue is empty
    And I schedule "test_command" with ID "test_id" to run at 15:00
    And I schedule "overwritten_test_command" with ID "test_id" to run at 14:00
    And I schedule "another_command" with ID "another_id" to run at 15:01
    And I schedule "yet_another_command" with ID "yet_another_id" to run at 15:02
    And the time is 14:50
    Then the command with ID "test_id" should have a status of "scheduled"
    And the command with ID "another_id" should have a status of "scheduled"
    When I run the scheduler worker
    Then there should be 0 commands in the queue
    Then the time is 15:01
    When I run the scheduler worker
    Then there should be 2 commands in the queue
    And the command with ID "test_id" should have a status of "queued"
    And the command with ID "another_id" should have a status of "queued"
    When I run the queue worker
    And I run the queue worker
    Then the command "overwritten_test_command" should have run
    And the command "another_command" should have run
    And the command with ID "test_id" should have a status of "completed"
    And the command with ID "another_id" should have a status of "completed"
    And there should be 0 commands in the queue
    And I run the scheduler worker
    And there should be 0 commands in the queue
    And the time is 15:03
    And I run the scheduler worker
    And there should be 1 commands in the queue
    And I run the queue worker
    And there should be 0 commands in the queue

  Scenario: Cancelling a scheduled command
    Given the queue is empty
    And I schedule "test_command" with id "test_command_id" to run at 15:00
    And the time is 14:50
    And I cancel "test_command_id"
    And the time is 15:01
    And I run the scheduler worker
    Then there should be 0 commands in the queue
    And the command should have a status of "not_found"

  Scenario: Clearing a queue
    Given I queue "test_command" with ID "id1"
    And I queue "another_command" with ID "id2"
    And I schedule "yet_another_command" with ID "id3" to run at 15:00
    And the time is 15:01
    When I clear the queue
    Then there should be 0 commands in the queue
    And I run the scheduler worker
    Then there should be 1 commands in the queue
    When I clear the queue
    And I run the scheduler worker
    Then there should be 0 commands in the queue

  Scenario: Deleting a queue
    Given I queue "test_command" with ID "id1"
    And I queue "another_command" with ID "id2"
    And I schedule "yet_another_command" with ID "id3" to run at 15:00
    And the time is 15:01
    When I delete the queue
    And I run the scheduler worker
    Then there should be 0 commands in the queue
    And the queue should have been deleted

  Scenario: Clearing the schedule
    Given the queue is empty
    And I schedule "test_command" with ID "test_id" to run at 15:00
    And I schedule "another_command" with ID "another_id" to run at 15:00
    And the time is 15:00
    When I clear the schedule
    And I run the scheduler worker
    Then there should be 0 commands in the queue
