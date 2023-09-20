<?php

namespace Tests\Feature\Commands;

use App\Models\Task;
use App\Models\User;
use DateTime;
use DateTimeZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DailyDeadlineReminderEmailTest extends TestCase
{

    use RefreshDatabase;

    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        // create the user
        $user = User::factory()->create();

        // insert two records to Task with today as due date and one task that is not due today
        $dueDate = (new DateTime('now'))->setTimezone(new DateTimeZone('Asia/Manila'));
        $dueDateTomorrow = (new DateTime('now'))->setTimezone(new DateTimeZone('Asia/Manila'))->modify('+1 Day');
        $task1 = new Task([
            'title' => 'Task 1',
            'description' => 'test',
            'due_date' => $dueDate,
            'status' => 0,
            'priority' => 5,
            'is_archived' => false,
            'order' => 1
        ]);

        $user->tasks()->save($task1);

        $task2 = new Task([
            'title' => 'Task 2',
            'description' => 'test',
            'due_date' => $dueDate,
            'status' => 0,
            'priority' => 5,
            'is_archived' => false,
            'order' => 1
        ]);
        $user->tasks()->save($task2);

        $task3 = new Task([
            'title' => 'Task 3',
            'description' => 'test',
            'due_date' => $dueDateTomorrow,
            'status' => 0,
            'priority' => 5,
            'is_archived' => false,
            'order' => 1
        ]);

        $user->tasks()->save($task3);

        // run the command and check if it successfully retrieves all due task for today
        Artisan::call('app:send-due-reminder-email');


        // send the email

        // test if emails are sent
    }
}
