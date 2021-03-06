<?php

namespace go1\util\tests\task;

use go1\util\schema\mock\TaskMockTrait;
use go1\util\task\Task;
use go1\util\task\TaskHelper;
use go1\util\task\TaskItem;
use go1\util\tests\UtilTestCase;

class TaskHelperTest extends UtilTestCase
{
    use TaskMockTrait;
    protected $taskService = 'service';
    private $taskName = 'service_task';
    private $taskItemName = 'service_task_item';

    public function testLoadTaskByStatus()
    {
        $this->createTask($this->go1, [
            'name' => $this->taskName,
            'data' => ['type' => 'task_type', 'lo_id' => 1000]
        ]);

        $task = TaskHelper::loadTaskByStatus($this->go1, Task::STATUS_PENDING, $this->taskName);
        $this->assertTrue($task instanceof Task);
        $this->assertEquals('task_type', $task->getDataType());
        $this->assertEquals(1000, $task->data['lo_id']);
    }

    public function testLoadTaskItemByStatus()
    {
        $taskId = $this->createTask($this->go1, [
            'name' => $this->taskName,
            'data' => ['type' => 'task_type', 'lo_id' => 1000]
        ]);

        $this->createTaskItem($this->go1, [
            'name'    => $this->taskItemName,
            'task_id' => $taskId,
            'data'    => ['type' => 'task_item_type', 'lo_id' => 1000]
        ]);

        $taskItem = TaskHelper::loadTaskItemByStatus($this->go1, $taskId, Task::STATUS_PENDING, $this->taskItemName);
        $this->assertTrue($taskItem instanceof TaskItem);
        $this->assertEquals('task_item_type', $taskItem->getDataType());
        $this->assertEquals(1000, $taskItem->data['lo_id']);
    }

    public function testChecksumTaskCompletedWithDateNotExpired()
    {
        $this->createTask($this->go1, [
            'name'    => $this->taskName,
            'created' => strtotime('2 days'),
            'data'    => $data = ['type' => 'task_type_other', 'lo_id' => 1000],
            'status'  => Task::STATUS_COMPLETED,
        ]);
        $this->assertFalse(TaskHelper::checksum($this->go1, $this->taskName, json_encode($data)));
    }

    public function testChecksumTaskCompletedWithDateExpired()
    {
        $this->createTask($this->go1, [
            'name'    => $this->taskName,
            'created' => strtotime('-1 days'),
            'data'    => $data = ['type' => 'task_type_other', 'lo_id' => 1000],
            'status'  => Task::STATUS_COMPLETED,
        ]);
        $this->assertFalse(TaskHelper::checksum($this->go1, $this->taskName, json_encode($data)));
    }

    public function testChecksumTaskFailedWithDateNotExpired()
    {
        $this->createTask($this->go1, [
            'name'    => $this->taskName,
            'created' => strtotime('2 days'),
            'data'    => $data = ['type' => 'task_type_other', 'lo_id' => 1000],
            'status'  => Task::STATUS_FAILED,
        ]);
        $this->assertFalse(TaskHelper::checksum($this->go1, $this->taskName, json_encode($data)));
    }

    public function testChecksumTaskFailedWithDateExpired()
    {
        $this->createTask($this->go1, [
            'name'    => $this->taskName,
            'created' => strtotime('-1 days'),
            'data'    => $data = ['type' => 'task_type_other', 'lo_id' => 1000],
            'status'  => Task::STATUS_FAILED,
        ]);
        $this->assertFalse(TaskHelper::checksum($this->go1, $this->taskName, json_encode($data)));
    }

    public function testChecksumTaskPendingWithDateExpired()
    {
        $this->createTask($this->go1, [
            'name'    => $this->taskName,
            'created' => strtotime('-2 days'),
            'data'    => $data = ['type' => 'task_type_other', 'lo_id' => 1000],
            'status'  => Task::STATUS_PENDING,
        ]);
        $this->assertFalse(TaskHelper::checksum($this->go1, $this->taskName, json_encode($data)));
    }

    public function testChecksumTaskPendingWithDateNotExpired()
    {
        $this->createTask($this->go1, [
            'name'    => $this->taskName,
            'created' => strtotime('2 days'),
            'data'    => $data = ['type' => 'task_type_other', 'lo_id' => 1000],
            'status'  => Task::STATUS_PENDING,
        ]);
        $this->assertTrue(TaskHelper::checksum($this->go1, $this->taskName, json_encode($data)));
    }

    public function testChecksumNoData()
    {
        $this->assertFalse(TaskHelper::checksum($this->go1, $this->taskName, []));
    }
}