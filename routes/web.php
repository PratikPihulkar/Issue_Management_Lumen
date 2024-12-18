<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->options('/{any:.*}', function () {
    return response('', 200);
});

$router->get('/', function () use ($router) {
    return $router->app->version();
});

//Company Registration Route
$router->post('/company','AuthController@register');

//Admin,Manager and Developer Login Route
$router->post('/login','AuthController@login');

//Token refresh Route
$router->post('/refresh','AuthController@refresh');

//Authenticated Routes
$router->group(['middleware' => ['auth:api', 'verify_access']], function() use ($router){

    //Admin Routes
    $router->get('/me','AuthController@me');
    $router->post('/logout','AuthController@logout');
    $router->post('/user','AdminController@addUser');
    $router->delete('/user','AdminController@removeUser');
    $router->get('/user[/{id}]','AdminController@getUser');
    $router->patch('/user/{id}','AdminController@updateUser');
    $router->post('/projects','AdminController@createProject');
    $router->get('/projects/{companyId}','AdminController@getProjects');
    $router->post('/projectDetails/{projectId}','AdminController@projectDetails');
    $router->post('/get-members-to-add','AdminController@getMembersToAddInProject');
    $router->post('/project/add-member','AdminController@addMemberInProject');    

    //Manager Routes
    $router->post('/project','ManagerController@createProject');
    $router->patch('/project/{id}','ManagerController@updateProject');
    $router->post('/project/assign-developer','ManagerController@assignDevelopers');

    //Developer Routes
    $router->get('/get-project/{id}','DeveloperController@getAssignedProject');
    $router->post('/issues','DeveloperController@raiseIssue');
    $router->get('/issues/{projectId}','DeveloperController@getIssues');
    $router->patch('/issues/{id}','DeveloperController@updateIssue');

});

//Issue Routes
$router->get('/all-issues/{companyId}','IssueController@getAllIssues');
$router->patch('/update-issues/{id}','IssueController@updateIssue');











