<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Models\Tag;
use App\Models\Task;
use Auth;
use Illuminate\Support\Facades\DB;
use Response;

class TaskController extends Controller
{
    private const ITEMS_PER_PAGE = 12;

    public function __construct(private StoreTaskRequest $request)
    {
    }

    /**
    * @OA\Post(
    * path="/api/task",
    * operationId="TaskCreate",
    * tags={"Task"},
    * summary="Create Task",
    * description="Create Task",
    * security={{"sanctum":{}}},
    *     @OA\RequestBody(
    *         @OA\JsonContent(
    *           ref="#/components/schemas/Task"
    *         ),
    *    ),
    *    @OA\Response(
    *          response=200,
    *          description="Register Successfully",
    *          @OA\JsonContent(
    *           ref="#/components/schemas/Task"
    *          ),
    *       ),
    *    @OA\Response(
    *          response=422,
    *          description="Unprocessable Entity",
    *          @OA\JsonContent(
    *               ref="#/components/schemas/ValidationErrorResponseJson"
    *          ),
    *     ),
    * )
    **/
    public function create()
    {
        $task = Task::create($this->request);
        if (null !== $this->request['tags']) {
            foreach($this->request['tags'] as $tag) {
                $tag = Tag::find($tag['id']);
            }
            $task->tags()->save($tag);
        }

        Response::json([
            'data' => $task
        ], 201);
    }

    /**
    * @OA\Put(
    * path="/api/task/{id}",
    * operationId="UpdateTask",
    * tags={"Task"},
    * summary="Update Task",
    * description="Update Task",
    * security={{"sanctum":{}}},
    *     @OA\Parameter(
    *         name="id",
    *         description="Id of the task",
    *         in="path",
    *         required=true
    *     ),
    *     @OA\RequestBody(
    *         @OA\JsonContent(
    *           ref="#/components/schemas/Task"
    *         ),
    *    ),
    *    @OA\Response(
    *          response=200,
    *          description="Register Successfully",
    *          @OA\JsonContent(
    *               ref="#/components/schemas/Task"
    *          ),
    *    ),
    *    @OA\Response(
    *          response=401,
    *          description="You can only update your own task",
    *    ),
    *    @OA\Response(
    *          response=422,
    *          description="Unprocessable Entity",
    *          @OA\JsonContent(
    *               ref="#/components/schemas/ValidationErrorResponseJson"
    *          ),
    *    ),
    * )
    **/
    public function update($id)
    {
        $data = $this->request->all();

        // check if task exists
        $task = Task::findOrFail($id);
        $this->checkUserIsOwner($task, 'You can only update your own task');

        Task::where('id', $id)->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'due_date' => $data['due_date'],
            'status' => $data['status'],
            'priority' => $data['priority'],
            'is_archived' => $data['is_archived'],
            'order' => $data['order']
        ]);

        // clear all tags of task
        $task->tags()->detach();

        if (null !== $this->request['tags']) {
            foreach($this->request['tags'] as $tag) {
                $tagId = $tag['id'];
                $tag = Tag::find($tagId);
                $task->tags()->save($tag);
            }
        }

        return Response::json([], 200);
    }

    public function getById($id)
    {
        // check if task exists
        $task = Task::findOrFail($id);
        $this->checkUserIsOwner($task, 'You can only view your own task');

        return Response::json(['data' => $task], 200);
    }

    /**
    * @OA\Delete(
    * path="/api/task/{id}",
    * operationId="DeleteTask",
    * tags={"Task"},
    * summary="Delete Task",
    * description="Delete Task",
    * security={{"sanctum":{}}},
    *     @OA\Parameter(
    *         name="id",
    *         description="Id of the task",
    *         in="path",
    *         required=true
    *     ),
    *    @OA\Response(
    *          response=200,
    *          description="Register Successfully",
    *    ),
    *    @OA\Response(
    *          response=401,
    *          description="You can only delete your own task",
    *     ),
    * )
    **/
    public function delete($id)
    {
        $task = Task::findOrFail($id);
        $this->checkUserIsOwner($task, 'You can only delete your own task');
        $task->delete();
        return Response::json(['message' => 'task deleted'], 202);
    }

    /**
    * @OA\Get(
    * path="/api/task",
    * operationId="GetAllUserTask",
    * tags={"Task"},
    * summary="Get all task",
    * description="Get all task task of the authenticated user",
    * security={{"sanctum":{}}},
    *     @OA\Parameter(
    *         name="searchKey",
    *         description="Search key for the title field",
    *         in="query",
    *         required=false,
    *         example="My todo title"
    *     ),
    *     @OA\Parameter(
    *         name="page",
    *         description="selects the number of page in the paginated data",
    *         in="query",
    *         required=false,
    *         example="1"
    *     ),
    *     @OA\Parameter(
    *         name="sortBy",
    *         description="field name of what to sort",
    *         in="query",
    *         required=false,
    *         example="priority",
    *     ),
    *     @OA\Parameter(
    *         name="sortOrder",
    *         description="Sort order, either 'asc' or 'desc'",
    *         in="query",
    *         required=false,
    *         example="asc",
    *     ),
    *     @OA\Response(
    *          response=200,
    *          description="List of task",
    *          @OA\JsonContent(
    *               @OA\Property(
    *                   property="data",
    *                   type="array",
    *                   description="List of task",
    *                   @OA\Items(
    *                       ref="#/components/schemas/Task"
    *                   )
    *               ),
    *               @OA\Property(
    *                   property="current_page",
    *                   type="string",
    *                   description="The current page",
    *               ),
    *               @OA\Property(
    *                   property="next_page_url",
    *                   type="string",
    *                   description="The url link for the next page",
    *               ),
    *               @OA\Property(
    *                   property="per_page",
    *                   type="integer",
    *                   description="The items per page",
    *               ),
    *               @OA\Property(
    *                   property="total",
    *                   type="integer",
    *                   description="The number of total items",
    *               ),
    *          ),
    *    ),
    * )
    **/
    public function getAll()
    {
        $searchKey = $this->request->query('searchKey') ?? null;

        $tasks = DB::table('tasks as t')
            ->where('t.user_id', '=', Auth::user()->id)
            ->when($searchKey, function($query, $searchKey){
                return $query->where("t.title", "ilike", "%{$searchKey}%");
            })
            ->orderBy($this->sanitizedSortBy(), $this->sanitizedSortOrder())
            ->paginate(self::ITEMS_PER_PAGE);

        return Response::json($tasks, 200);
    }

    private function sanitizedSortBy(): string
    {
        $sortBy = $this->request->query('sortBy') ?? 'id';
        if (in_array($sortBy, (new Task())->getSortFields()))
        {
           return $sortBy;
        }

        return Task::DEFAULT_SORT_FIELD;
    }

    private function sanitizedSortOrder(): string
    {
        $orderBy = $this->request->query('sortOrder') ?? 'asc';
        if (in_array($orderBy, ['asc', 'desc']))
        {
            return $orderBy;
        }

        return 'asc';
    }

    private function checkUserIsOwner(Task $task, $errorMessage): void
    {
        // check if user is not the creator of task
        if ($task->user()->first()->id !== Auth::user()->id){
            // throw 401 exception error
            abort(401, $errorMessage);
        }
    }

    /**
    * @OA\Get(
    * path="/api/task/{id}/tag",
    * operationId="GetAllTaskTags",
    * tags={"Task"},
    * summary="Get all tags of a given task",
    * description="Get all tags of a given task",
    * security={{"sanctum":{}}},
    *     @OA\Parameter(
    *         name="id",
    *         description="Search key for the title field",
    *         in="path",
    *         required=true,
    *         example="1"
    *     ),
    *     @OA\Response(
    *          response=200,
    *          description="List of tags of a given task",
    *          @OA\JsonContent(
    *               type="array",
    *               @OA\Items(ref="#/components/schemas/Tag")
    *          )
    *    ),
    * )
    **/
    public function getTaskTags($id)
    {
        $task = Task::findOrFail($id);
        return $task->tags()->get();
    }
}
