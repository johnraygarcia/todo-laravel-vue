<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Task;
use Auth;
use Illuminate\Http\Request;
use Storage;

class AttachmentController extends Controller
{
    public function __construct(private Request $request)
    {

    }

    public function upload($id)
    {
        $this->request->validate([
            'file' => 'required|mimes:mp4,jpeg,jpg,png,csv,txt,doc,docx|max:5000'
        ]);

        $task = Task::findOrfail($id);
        $this->checkUserIsOwner($task);
        $uploadedFile = $this->request->file('file');
        $oExtension = $uploadedFile->getClientOriginalExtension();
        $oFilename = $uploadedFile->getClientOriginalName();
        $filename = preg_replace('/[^a-zA-Z0-9_-]+/', '-', strtolower($oFilename)).'-'.time().'.'.$oExtension;

        // upload the file
        $uploadedFile->storeAs('uploads', $filename, 'public');

        $attachment = Attachment::factory()->create([
            'filename' => $filename,
            'type' => $oExtension,
            'task_id' => $task->id
        ]);

        $task->attachments()->save($attachment);

    }

    public function delete($taskId, $attachmentId)
    {
        $task = Task::findOrFail($taskId);
        $this->checkUserIsOwner($task);
        $attachment = Attachment::where([
            'id' => $attachmentId,
            'task_id' => $taskId
        ])->first();

        if ($attachment) {
            Storage::delete($attachment->filename);
            $attachment->delete();
        }
        abort(404, 'Attachment not found');
    }

    private function checkUserIsOwner(Task $task): void
    {
        // check if user is not the creator of task
        if ($task->user()->first()->id !== Auth::user()->id){
            // throw 401 exception error
            abort(401, 'You can only upload files on your own task');
        }
    }

    public function getAttachmentsByTask($taskId)
    {
        $task = Task::findOrFail($taskId);
        $this->checkUserIsOwner($task);
        $attachments = Attachment::where('task_id', $taskId)->get();
        return response([
            'data' => $attachments
        ]);
    }
}
