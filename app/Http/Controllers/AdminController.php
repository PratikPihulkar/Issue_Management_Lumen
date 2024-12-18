<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Models\Project;
use App\Models\Issue;
use App\Models\ProjectDeveloper;
use App\Mail\WelcomeUserMail; 
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Jobs\SendWelcomeUserEmail;
use Illuminate\Support\Facades\Cache;

class AdminController extends Controller
{
    //To add Manager and Developer
    public function addUser(Request $request)
    {
        try{

            $this->validate($request,[
                'company_id' => 'required|exists:users,id',
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|confirmed',
                'role' => 'required|in:Manager,Developer'
            ]);

            $companyId = $request->input('company_id');
            $name = $request->input('name');
            $email = $request->input('email');
            $password = Hash::make($request->input('password'));
            $role = $request->input('role');
            $details = [ 
                'title' => 'Welcome to Issue Management App', 
                'body' => 'Your profile has been created on Issue Management App. 
                            Your login credentials are : 
                            email : '. $email . '
                            password : '. $request->input('password') . '
                            We suggest to change your password.',
                'email' => $email            
            ];

            $user = User::create([
                'company_id' => $companyId,
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => $role
            ]);

            if($user)
            {
                // Mail::to($email)->send(new WelcomeUserMail($details));
                dispatch(new SendWelcomeUserEmail($details));

                return response()->json([
                    'status' => 'success',
                    'message' => 'User Added in the company',
                    'data' => null
                ],200);
            }

        }catch(Exception $e)
        {
            Log::error('User Registration Failed',$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'User Registration Failed',
                'data' => null
            ],500);
        }
    }

    //To remove Manager and Developer by id
    public function removeUser(Request $request)
    {
        try{
            $this->validate($request,[
                'id' => 'required|integer'
            ]);
    
            $id = $request->input('id');
    
            $user = User::find($id);
    
            if(!$user)
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User Not Found!',
                    'data' => null
                ],404);
            }
    
            if($user->delete())
            {
                return response()->json([
                    'status' => 'success',
                    'message' => 'User Deleted Successfully',
                    'data' => null
                ],200);
            }
        }catch(Exception $e)
        {
            Log::error('Failed to delete User',$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to Delete User',
                'data' => null
            ],500);
        }
        
    }

    //To get all users and also user by id and also user by role 'Manager or Developer'
    public function getUser(Request $request, $id = null)
    {
        try{

            $role = $request->input('role');
            $companyId = $request->input('company_id');

            if($id)
            {
                $cacheKey = "user_{$id}_role_{$role}";

                //check if user is cached
                $user = Cache::remember($cacheKey, 3600, function() use ($id,$role){

                    $user = User::find($id);

                    if($user && (!$role || $user->role === $role))
                    {
                        return $user;
                    }

                    return null;
                });

                if(!$user)
                {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'User not found',
                        'data' => null
                    ],404);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'User Found',
                    'data' => $user
                ],200);
            }

            //if no specific user $id then fetch all users
            $cacheKey = "users_company_{$companyId}_role_{$role}";

            $users = Cache::remember($cacheKey, 3600, function() use ($role, $companyId){

                $usersQuery = User::query()
                ->leftJoin('projects', 'projects.manager_id', '=', 'users.id')
                ->leftJoin('users as managers', 'projects.manager_id', '=', 'managers.id') // Alias for manager name
                ->select(
                    'users.id',
                    'users.name',
                    'users.email',
                    'projects.name as project_name',
                    'users.role',
                    'managers.name as manager_name' // Manager's name or null if not assigned
                )
                ->where('users.company_id', $companyId);

                if($role)
                {
                    $usersQuery->where('users.role', $role);
                }

                return $usersQuery->get();

            });

            if($users->isEmpty())
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Users not found',
                    'data' => null
                ],404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'All users Retrieved',
                'data' => $users
            ],200);

        }catch(Exception $e)
        {
            Log::error('Failed to fetch data', $e->getMessage());
            return rsponse()->json([
                'status' => 'error',
                'message' => 'Failed to fetch data',
                'daya' => null
            ],500);
        }
    }

    //To update user (name, email or role)
    public function updateUser(Request $request, $id)
    {
        try{

            $this->validate($request, [
                'name' => 'string',
                'email' => 'email|unique:users,email',
                'role' => 'in:Manager,Developer'
            ]);
    
            $user = User::find($id);
    
            if(!$user)
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                    'data' => null
                ],404);
            }
    
            if($request->has('name'))
            {
                $user->name = $request->input('name');
            }
    
            if($request->has('email'))
            {
                $user->email = $request->input('email');
            }
    
            if($request->has('role'))
            {
                $user->role = $request->input('role');
            }
    
            if($user->save())
            {
                return response()->json([
                    'status' => 'success',
                    'message' => 'User updates successfully',
                    'data' => null
                ],200);
            }

        }catch(Exception $e)
        {
            Log::error('Failed to update user', $e->getMessage());
            return response()->json([
                'ststus' => 'error',
                'message' => 'Failed to update user',
                'data' => null
            ],500);
        }

    }

    //Fetching all projects
    public function getProjects($companyId)
    {
        try{

            $query = Project::query()
            ->join('users', 'projects.manager_id', '=', 'users.id')
            ->select(
                'projects.company_id',
                'projects.id as project_id',
                'projects.name as project_title',
                'users.name as manager',
                'projects.description as project_description',
                'projects.start_date',
                'projects.end_date'
            );

            $projects = $query->where('projects.company_id',$companyId)->get();

            if($projects->isEmpty())
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No projects found for your company',
                    'data' => null
                ],404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Projects fetched successfully',
                'data' => $projects
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

    //Single project details
    public function projectDetails(Request $request, $projectId)
    {
        try{

            $companyId = $request->input('company_id');

            $issueQuery = Issue::query()
            ->join('users', 'issues.developer_id', '=', 'users.id')
            ->select(
                'issues.id as issue_id',
                'issues.title as issue_title',
                'issues.description as issue_description',
                'issues.status as issue_status',
                'issues.priority as issue_priority',
                'issues.deadline',
                'users.name as creater' 
            )
            ->where('issues.company_id', $companyId)
            ->where('issues.project_id', $projectId);

            $projectMembersQuery = ProjectDeveloper::query()
            ->join('users', 'project_developers.developer_id', '=', 'users.id')
            ->select(
                'project_developers.developer_id as emp_id',
                'users.name',
                'project_developers.project_role',
                'users.email'
            )
            ->where('project_developers.company_id', $companyId)
            ->where('project_developers.project_id', $projectId);

            $issues = $issueQuery->get();
            $members = $projectMembersQuery->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Issues fetced successfully',
                'data' => [
                    'issues' => $issues,
                    'members' => $members
                ]
            ],200);

        }catch(Exception $e)
        {
            Log::error('Failed to fetch project details', $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch project details',
                'data' => null
            ],500);
        }
    }

    public function getMembersToAddInProject(Request $request)
    {
        try{

            $companyId = $request->input('company_id');

            $query = User::query()
            ->leftJoin('project_developers', 'users.id', '=', 'project_developers.developer_id')
            ->leftJoin('projects', 'users.id', '=', 'projects.manager_id')
            ->select('users.id', 'users.name')
            ->whereNull('project_developers.developer_id')
            ->whereNull('projects.manager_id');

            $members = $query->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Members fetched successfully',
                'data' => [
                    'members' => $members,
                    'roles' => ['Frontend_Developer', 'Backend_Developer', 'Tester', 'Designer']
                ]
            ],200);

        }catch(Exception $e)
        {
            Log::error('Failed to add member', $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add member',
                'data' => null
            ],500);
        }
    }

    public function addMemberInProject(Request $request)
    {
        try{

            $this->validate($request, [
                'company_id' => 'required|integer',
                'project_id' => 'required|integer',
                'developer_id' => 'required|integer',
                'project_role' => 'required|in:Frontend_Developer,Backend_Developer,Tester,Designer'
            ]);

            $companyId = $request->input('company_id');
            $projectId = $request->input('project_id');
            $developerId = $request->input('developer_id');
            $projectRole = $request->input('project_role');

            $memberAdded = ProjectDeveloper::create([
                'company_id' => $companyId,
                'project_id' => $projectId,
                'developer_id' => $developerId,
                'project_role' => $projectRole
            ]);

            if($memberAdded)
            {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Member added successfully',
                    'data' => null
                ],200);
            }

        }catch(Exception $e)
        {
            Log::error('Failed to add member in project', $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add member in project',
                'data' => null
            ],200);
        }
    }

    //Create project
    public function createProject(Request $request)
    {
        try{

            $this->validate($request, [
                'company_id' => 'required|integer|exists:users,id',
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
}
