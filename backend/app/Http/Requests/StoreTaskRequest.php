<?php

namespace App\Http\Requests;

use App\Models\Task;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class StoreTaskRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if route is update. Check if the user is the owner of the task
        if (request()->method() === "PUT") {
            $task = Task::findOrFail(app('request')->segment(3));
            if($task->user_id !== Auth::user()->id) {
                abort(401, 'You can only update your own task');
            }
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required',
            'status' => 'required',
            'priority' => 'required',
            'order' => 'required'
        ];
    }
}
