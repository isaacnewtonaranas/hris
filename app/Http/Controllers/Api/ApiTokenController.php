<?php

namespace App\Http\Controllers\Api;

use App\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ApiTokenRequest;
use GuzzleHttp\Exception\ServerException;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class ApiTokenController extends Controller
{
    public function getToken(ApiTokenRequest $request)
    {   
        // Validate
        $credentials = $request->validated();
        // Check if username or email is used
        $credentials = $this->convertUsernameToEmail((Object) $credentials);
        // Request Token
        return $this->requestTokenFromServer($credentials);

    }

    public function removeToken()
    {
       Auth::user()->tokens->each(function($token, $key) {
            $token->delete();
        });

        return ResponseBuilder::asSuccess(200)
            ->withMessage('User token successfully removed.')
            ->withHttpCode(200)
            ->build();
    }

    protected function convertUsernameToEmail($credentials)
    {
        $user = User::where('username', $credentials->username)->first();

        $login_type = filter_var($credentials->username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if($login_type === 'username' && $user !== null) {
           $credentials->username = $user->email;
        }

        return $credentials;
    }

    protected function requestTokenFromServer($credentials)
     {
        try 
        {
            $http = new Client();

            $response = $http->post(route('passport.token'), [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => config('passport.password_grant_id'),
                    'client_secret' => config('passport.password_grant_secret'),
                    'username' => $credentials->username,
                    'password' => $credentials->password,
                    'scope' => ''
                ],
            ]);

             return ResponseBuilder::asSuccess(200)
                ->withData((array) json_decode($response->getBody()))
                ->withHttpCode(200)
                ->build();

        } catch (\Exception $e) 
        {
            if($e instanceof ServerException)
            {
                return ResponseBuilder::asError(500)
                    ->withMessage('Server Error. Please contact administrator.')
                    ->withHttpCode(500)
                    ->build();    
            }

            return ResponseBuilder::asError(401)
                    ->withMessage('Invalid username and password.')
                    ->withHttpCode(401)
                    ->build();
        }
    }

}
