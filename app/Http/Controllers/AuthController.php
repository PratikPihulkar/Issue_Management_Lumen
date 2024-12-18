<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Mail\WelcomeCompanyMail; 
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendWelcomeCompanyEmail;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try{

            $this->validate($request,[
                'name' => 'required|string',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|confirmed',
            ],
            [
                'email.unique' => 'This email address is already registered.'
            ]);

            $name = $request->input('name');
            $email = $request->input('email');
            $password = Hash::make($request->input('password'));
            

            $company = User::create([
                'company_id' => null,
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => 'Admin'
            ]);

            if($company)
            {
                $details = [ 
                    'title' => 'Welcome to Issue Management App', 
                    'body' => 'Thank you for trusting our App',
                    'email' => $email
                ];

                // Mail::to($email)->send(new WelcomeCompanyMail($details));
                dispatch(new SendWelcomeCompanyEmail($details));
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Company registered successfully',
                    'data' => null
                ],200);
            }


        }catch(Exception $e)
        {
            Log::error('Registration error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'could not create user',
                'data' => null
            ],500);
        }
    }

    public function login(Request $request)
    {
        try{
            
            $this->validate($request,[
                'email' => 'required|email',
                'password' => 'required'
            ],
            [
               'email.email' => [
                    'status' => 'error',
                    'message' => 'You entered wrong email address',
                    'data' => null
                ]
            ]);

            $email = $request->input('email');

            
            $user = User::where('email', $email)->first();

            
            if(!$user)
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found!',
                    'data' => null
                ], 404);
            }

            $userRole = User::query()
            ->select('role')
            ->where('email', $email)
            ->get();

            $credentials = $request->only(['email','password']);

            //Attempt to verify credentials and create access token
            if(!$accessToken = Auth::claims(['token_type' => 'access', 'role' => $userRole])->attempt($credentials))
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid Credentials',
                    'data' => null
                ],401);
            }

            //Refresh token generation
            $user = Auth::user();
            $refreshToken = JWTAuth::claims(['token_type' => 'refresh'])->fromUser($user);

            //Return both tokens
            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken,
                    'token_type' => 'bearer',
                    'expires_in' => auth()->factory()->getTTL() * 60
                ]
            ],200);

        }catch(Exception $e)
        {
            Log::error('Login Failed',$e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to login!',
                'data' => null
            ],500);
        }
    }

    public function me()
    {
        try{

            return response()->json([
                "status" => "success",
                "message" => "User retrieved successfully.",
                "data" => [auth()->user()]
            ],200);
            
        }catch (\Exception $e) {

            Log::error("User retrieval error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while retrieving user details',
                'data' => null
            ], 500);

        }
    }

    public function logout(Request $request)
    {
        
        try {
            // Get the access token from the Authorization header
            $accessToken = auth()->getToken(); 
    
            // Invalidate the access token
            auth()->logout();
    
            // Retrieve the refresh token from the request body
            $refreshToken = $request->input('refresh_token');
            if (!$refreshToken) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Refresh token is required',
                    'data' => null
                ], 400);
            }
    
            // Decode the refresh token to get its payload
            $refreshTokenPayload = JWTAuth::setToken($refreshToken)->getPayload();
    
            // Ensure the token type is 'refresh'
            if ($refreshTokenPayload->get('token_type') !== 'refresh') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid token type. Only refresh tokens can be invalidated here.',
                    'data' => null
                ], 403);
            }
    
            // Invalidate the refresh token
            JWTAuth::invalidate(JWTAuth::setToken($refreshToken));
    
            return response()->json([
                "status" => "success",
                "message" => "User logged out and tokens invalidated successfully.",
                "data" => null
            ], 200);
    
        }catch(\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid token',
                'data' => null
            ], 401);

        }catch(\Exception $e) {

            Log::error("Logout error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while logging out',
                'data' => null
            ], 500);

        }
    }

    public function refresh()
    {		
        try {

            if(!JWTAuth::getToken())
            {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Token is required',
                    'data' => null
                ], 400);
            }
            
            $token = JWTAuth::getPayload(JWTAuth::getToken());
            if ($token->get('token_type') !== 'refresh') {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Only refresh tokens can be used here',
                    'data' => null
                ], 403);
            }
    
            
            // Generate a new access token from the refresh token
            $newAccessToken = JWTAuth::claims(['token_type' => 'access'])->fromUser(auth()->user());
            
    
            return response()->json([
                'new_access_token' => $newAccessToken,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);
    
        } catch (JWTException $e) {

            return response()->json([
                'status' => 'error', 
                'message' => 'Could not refresh token',
                'data' => null
            ], 500);
            
        }catch (\Exception $e) {

            Log::error("Token refresh error: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while refreshing token',
                'data' => null
            ], 500);

        }
    }
}
