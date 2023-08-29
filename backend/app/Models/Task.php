<?php

namespace App\Models;

use Auth;
use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class Task extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'description', 'due_date', 'status', 'priority', 'is_archived', 'order'];
    protected $sortFields = ['id', 'title', 'description', 'due_date', 'created_at', 'status', 'priority', 'is_archived', 'order'];
    public const DEFAULT_SORT_FIELD = 'id';
    public const STATUS_PENDING = 1;

    public static function create(Request $request)
    {
        $task = new Task($request->all());
        $user = Auth::user();
        $user->tasks()->save($task);
        return $task;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getSortFields(): array
    {
        return $this->sortFields;
    }


}
