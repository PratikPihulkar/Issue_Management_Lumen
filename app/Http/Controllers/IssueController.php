<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Issue;
use Illuminate\Support\Facades\Log;

class IssueController extends Controller
{
    public function getAllIssues($companyId)
    {
        try{

            $issues = Issue::where('company_id', $companyId)->get();

            if($issues->isEmpty())
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Issues not found',
                    'data' => null
                ],404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Issues fetched successfully',
                'data' => $issues
            ],200);

        }catch(Exception $e)
        {
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

            $this->validate($request,[
                'title' => 'string', 
                'description' => 'string', 
                'status' => 'in:Open,In-Progress,Resolved,Closed', 
                'priority' => 'in:Low,Mediun,High', 
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
            Log::error('Failed to update issue', $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update issue',
                'data' => null
            ],500);
        }
    }
}
