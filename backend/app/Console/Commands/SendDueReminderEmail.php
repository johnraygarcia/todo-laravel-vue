<?php

namespace App\Console\Commands;

use App\Mail\SendDueDateReminderEmail;
use App\Models\Task;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDueReminderEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-due-reminder-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Daily job that checks if there are task due today for each user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dtStart = (new DateTime('now'))->format('Y-m-d 00:00:00');
        $dtEnd = (new DateTime('now'))->modify('+1 Day')->format('Y-m-d 00:00:00');
        $tasks = Task::whereRaw('date(due_date) >= ?', [$dtStart])->whereRaw('date(due_date) < ?', [$dtEnd])->get();

        // send email
        foreach($tasks as $task) {
            Mail::to($task->user->email)->send(new SendDueDateReminderEmail($task));
        }
    }
}
