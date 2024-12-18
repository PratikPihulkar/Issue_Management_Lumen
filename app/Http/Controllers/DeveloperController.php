<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectDeveloper;
use App\Models\Issue;
use Illuminate\Support\Facades\Log;

class DeveloperController extends Controller
{
    public function getAssignedProject($id)
    {
        try{
            $assignedProject = ProjectDeveloper::where('developer_id', $id)->get();

            if($assignedProject->isEmpty())
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No assigned project found',
                    'data' => null
                ],404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Assigned project fetched',
                'data' => $assignedProject
            ],200);

        }catch(Exception $e)
        {
            Log::error('Failed to fetch project', $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch project',
                'data' => null
            ],500);
        }
        
    }

    public function raiseIssue(Request $request)
    {
        try{

            $this->validate($request, [
                'company_id' => 'required|integer',
                'project_id' => 'required|integer|exists:projects,id',
                'developer_id' => 'required|integer|exists:users,id',
                'title' => 'required|string',
                'description' => 'required|string',
                'status' => 'required|in:Open,In-Progress,Resolved,Closed',
                'priority' => 'required|in:Low,Medium,High',
                'deadline' => 'required|date'
            ]);

            $companyId = $request->input('company_id');
            $projectId = $request->input('project_id');
            $developerId = $request->input('developer_id');
            $title = $request->input('title');
            $description = $request->input('description');
            $status = $request->input('status');
            $priority = $request->input('priority');
            $deadline = $request->input('deadline');

            $issue = Issue::create([
                'company_id' => $companyId,
                'project_id' => $projectId,
                'developer_id' => $developerId,
                'title' => $title,
                'description' => $description,
                'status' => $status,
                'priority' => $priority,
                'deadline' => $deadline
            ]);

            if($issue)
            {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Issue raised successfully',
                    'data' => $issue
                ],200);
            }

        }catch(Exception $e)
        {
            Log::error('Failed to raise issue', $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to raise issue',
                'data' => null
            ],500);
        }
    }

    public function getIssues($projectId)
    {
        try{

            $issues = Issue::where('project_id',$projectId)->get();

            if($issues->isEmpty())
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No issues found for the given project ID',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Issues fetched successfully',
                'data' => $issues
            ],200);

        }catch(Exception $e)
        {
            Log::error('Failed to fetch issues', $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch issues',
                'data' => null
            ],500);
        }
    }

    public function updateIssue(Request $request, $id)
    {
        try{

            $issue = Issue::find($id);

            $this->validate($request, [
                'title' => 'string', 
                'description' => 'string', 
                'status' => 'in:Open,In-Progress,Resolved,Closed', 
                'priority' => 'in:Low,Medium,High', 
                'deadline' => 'date'
            ]);

            if($request->has('title'))
            {
                $issue->title = $request->input('title');
            }

            if($request->has('description'))
            {
                $issue->description = $request->input('description');
            }

            if($request->has('status'))
            {
                $issue->status = $request->input('status');
            }

            if($request->has('priority'))
            {
                $issue->priority = $request->input('priority');
            }

            if($request->has('deadline'))
            {
                $issue->deadline = $request->input('deadline');
            }

            if($issue->save())
            {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Issue updated successfully',
                    'data' => null
                ],200);
            }

        }catch(Exception $e)
        {
            Log::error('Failed to fetch issues', $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update issue',
                'data' => null
            ],500);
        }
    }
}
