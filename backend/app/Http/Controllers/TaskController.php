<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Models\Task;
use Auth;
use Response;

class TaskController extends Controller
{
    private const ITEMS_PER_PAGE = 5;

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
        return Response::json([
            'data' => Task::create($this->request)
        ], 201);
    }

    /**
    * @OA\Put(
    * path="/api/task/{id}",
    * operationId="UpdateTask",
    * tags={"Task"},
    * summary="Update Task",
    * description="Update Task",
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
    *          response=422,
    *          description="Unprocessable Entity",
    *          @OA\JsonContent(
    *               ref="#/components/schemas/ValidationErrorResponseJson"
    *          ),
    *     ),
    * )
    **/
    public function update($id)
    {
        $data = $this->request->all();

        // check if task exists
        $task = Task::findOrFail($id);
        $this->checkUserIsOwner($task);

        Task::where('id', $id)->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'due_date' => $data['due_date'],
            'status' => $data['status'],
            'priority' => $data['priority'],
            'is_archived' => $data['is_archived'],
            'order' => $data['order']
        ]);

        return Response::json([], 200);
    }

    public function getById($id)
    {
        // check if task exists
        $task = Task::findOrFail($id);
        $this->checkUserIsOwner($task);

        return Response::json(['data' => $task], 200);
    }

    /**
    * @OA\Delete(
    * path="/api/task/{id}",
    * operationId="DeleteTask",
    * tags={"Task"},
    * summary="Delete Task",
    * description="Delete Task",
    *     @OA\Parameter(
    *         name="id",
    *         description="Id of the task",
    *         in="path",
    *         required=true
    *     ),
    *    @OA\RequestBody(
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
    *          response=422,
    *          description="Unprocessable Entity",
    *          @OA\JsonContent(
    *               ref="#/components/schemas/ValidationErrorResponseJson"
    *          ),
    *     ),
    * )
    **/
    public function delete($id)
    {
        $task = Task::findOrFail($id);
        $this->checkUserIsOwner($task);
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
    *    @OA\Response(
    *          response=200,
    *          description="Register Successfully",
    *          @OA\JsonContent(
    *               @OA\Property(
    *                   property="data",
    *                   type="array",
    *                   description="List of task",
    *               ),
    *               ref="#/components/schemas/Task"
    *          ),
    *    ),
    * )
    **/
    public function getAll()
    {
        $tasks = Task::where('user_id', Auth::user()->id)
            ->orderBy($this->sanitizedSortBy(), $this->sanitizedSortOrder())
            ->paginate(self::ITEMS_PER_PAGE);
        return Response::json([
            'data' => $tasks
        ], 200);
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

    private function checkUserIsOwner(Task $task): void
    {
        // check if user is not the creator of task
        if ($task->user()->first()->id !== Auth::user()->id){
            // throw 401 exception error
            abort(401, 'You can only update your own task');
        }
    }
}
