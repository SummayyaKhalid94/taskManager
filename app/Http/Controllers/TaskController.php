<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $tasks = Task::all();
        $project = null;
        if ($request->has('project')) {
            $project = Task::where('project_name', $request->project)->first();
        }
        if (!$project) {
            $project = Task::first() ? Task::first() : null;
        }

        // return $project;
        return view('tasks.index', compact('tasks', 'project'));
    }


    public function list(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value

        $totalRecords = Task::select('count(*) as allcount')->where('user_id', Auth::user()->id);
        if ($request->has('project')) {
            $totalRecords = $totalRecords->where('project_name', $request->project);
        }
        $totalRecords = $totalRecords->count();

        $totalRecordswithFilter = Task::select('count(*) as allcount')->where('user_id', Auth::user()->id)
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('priority', 'like', '%' . $searchValue . '%')
                    ->orWhere('project_name', 'like', '%' . $searchValue . '%');
            });
        if ($request->has('project')) {
            $totalRecordswithFilter = $totalRecordswithFilter->where('project_name', $request->project);
        }
        $totalRecordswithFilter = $totalRecordswithFilter->count();

        $records = Task::orderBy($columnName, $columnSortOrder);
        if ($request->has('project')) {
            $records = $records->where('project_name', $request->project);
        }
        $records = $records->where('user_id', Auth::user()->id)
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('priority', 'like', '%' . $searchValue . '%')
                    ->orWhere('project_name', 'like', '%' . $searchValue . '%');
            })
            ->select('*')
            ->skip($start)
            ->take($rowperpage)
            ->orderBy($columnName, $columnSortOrder)
            ->get();



        $data_arr = array();

        foreach ($records as $record) {
            $delete_route = route('task.destroy', $record->id);
            $show_route = route('task.show', $record->id);

            $data_arr[] = array(
                "id" => $record->id,
                "priority" => $record->priority,
                "name" => $record->name,
                "project_name" => $record->project_name,
                "created_at" => Carbon::parse($record->created_at)->format('F j, Y'),
                "updated_at" => Carbon::parse($record->updated_at)->format('F j, Y'),
                "action" => '
                    <ul class="orderDatatable_actions mb-0 d-flex flex-wrap">
                        <li>
                            <a href="#" onclick="loadTaskData(\'' . $show_route . '\',' . $record->id . ')" class="edit text-warning">
                                <i class="bx bx-edit"></i></a>
                        </li>
                        <li>
                            <a href="#" onclick="confirmDelete(\'' . $delete_route . '\')" class="remove text-danger">
                                <i class="bx bx-trash"></i></a>
                        </li>
                    </ul>'
            );
        }


        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );

        return response()->json($response);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'taskName' => 'required|string|max:255',
                'projectName' => 'required|in:Project A,Project B,Project C'
            ]);

            // Get the maximum priority for tasks with the same project_name
            $maxPriority = Task::where('project_name', $request->input('projectName'))
                ->max('priority');

            // Increment the max priority by 1 to set the new task's priority
            $priority = $maxPriority + 1;

            $task = new Task();
            $task->name = $request->input('taskName');
            $task->project_name = $request->input('projectName');
            $task->priority = $priority;
            $task->user_id = Auth::user()->id;
            $task->save();

            return response()->json(['message' => 'Task created successfully'], 200);
        } catch (Exception $e) {

            return response()->json([
                'message' => $e->getMessage(),
                'errors' => null,
                'result' => null
            ], 500);
        }
    }

    public function show($id)
    {
        $task = null;
        try {

            $task = Task::find($id);
            if (!$task) {
                throw new Exception('Task not found.');
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => null,
                'result' => null
            ], 500);
        }

        return response()->json([
            'message' => 'Request successful!',
            'errors' => null,
            'result' => $task
        ], 200);
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'taskName' => 'required|string|max:255',
                'projectName' => 'required|in:Project A,Project B,Project C'
            ]);

            $task = Task::findOrFail($id);

            // Update the task details
            $task->name = $request->input('taskName');
            $task->project_name = $request->input('projectName');

            $task->save();

            return response()->json(['message' => 'Task updated successfully'], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => null,
                'result' => null
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $task = Task::find($id);
            if (!$task) {
                throw new Exception('Task not found.');
            }

            $project_name = $task->project_name; // Store the project before deletion

            $task->delete();

            // Update the priority of the remaining tasks of the same project_name
            Task::where('project_name', $project_name)
                ->where('priority', '>', $task->priority)
                ->decrement('priority');

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
                'errors' => null,
                'result' => null
            ], 500);
        }

        return response()->json([
            'message' => 'Request Successful!',
            'errors' => null,
            'result' => null
        ], 200);
    }

    public function reorder(Request $request)
    {
        try {
            DB::beginTransaction();

            $reorderedData = $request->input('reorderedData');
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 100);

            // Calculate the offset based on the current page and the number of records per page
            $offset = ($page - 1) * $perPage;

            foreach ($reorderedData as $data) {
                $id = $data['id'];
                $newPosition = $data['new_position'] + $offset;
                Task::where('id', $id)->update(['priority' => ++$newPosition]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
                'errors' => null,
                'result' => null
            ], 500);
        }

        return response()->json([
            'message' => 'Request Successful!',
            'errors' => null,
            'result' => null
        ], 200);
    }
}
