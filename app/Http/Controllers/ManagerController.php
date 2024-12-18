<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Project;
use App\Models\User;
use App\Models\ProjectDeveloper;

class ManagerController extends Controller
{
    //Create project
    public function createProject(Request $request)
    {
        try{

            $this->validate($request, [
                'company_id' => 'required|integer|exists:companies,id',
                'manager_id' => 'required|integer|exists:users,id',
                'name' => 'required|string',
                'description' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date'
            ]);

            $companyId = $request->input('company_id');
            $managerId = $request->input('manager_id');
            $name = $request->input('name');
            $description = $request->input('description');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $project = Project::create([
                'company_id' => $companyId,
                'manager_id' => $managerId,
                'name' => $name,
                'description' => $description,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            if($project)
            {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Project created successfully',
                    'data' => null
                ],200);
            }

        }catch(Exception $e)
        {
            Log::error('Failed to create project', $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Project creation failed',
                'data' => null
            ],500);
        }
    }

    //Assign Developers to project and also the roles
    public function assignDevelopers(Request $request)
    {
        try{

            $this->validate($request, [
                'company_id' => 'required|integer|exists:companies,id',
                'project_id' => 'required|integer|exists:projects,id',
                'developer_id' => 'required|integer|exists:users,id',
                'project_role' => 'in:Frontend_Developer, Backend_Developer, Tester, Designer'
            ]);

            $companyId = $request->input('company_id');
            $projectId = $request->input('project_id');
            $developerId = $request->input('developer_id');
            $projectRole = $request->input('project_role');

            $assignedRole = ProjectDeveloper::create([
                'company_id' => $companyId,
                'project_id' => $projectId,
                'developer_id' => $developerId,
                'project_role' => $projectRole
            ]);

            if($assignedRole)
            {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Role assigned successfully',
                    'data' => null
                ],200);
            }

        }catch(Exception $e)
        {
            Log::error('Failed to assign', $e->getMessage());
            return response()->json([
                'status' => 'success',
                'message' => 'Failed to assign',
                'data' => null
            ],500);
        }
    }

    //Update project information
    public function updateProject(Request $request, $id)
    {
        try{

            $this->validate($request, [
                'name' => 'string',
                'description' => 'string',
                'start_date' => 'date',
                'end_date' => 'date'
            ]);

            $project = Project::find($id);

            if($request->has('name'))
            {
                $project->name = $request->input('name');
            }

            if($request->has('description'))
            {
                $project->description = $request->input('description');
            }

            if($request->has('start_date'))
            {
                $project->start_date = $request->input('start_date');
            }

            if($request->has('end_date'))
            {
                $project->end_date = $request->input('end_date');
            }

            if($project->save())
            {
                return response()->json([
                    'status' => 'success',
                    'message' => 'User success',
                    'date' => null
                ],200);
            }

        }catch(Exception $e)
        {
            Log::error('Failed to update', $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update',
                'date' => null 
            ],500);
        }
    }
}
