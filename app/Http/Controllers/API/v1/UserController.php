<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Exception;
use Validator;
use Mail;

class UserController extends Controller
{
    public function index(Request $request)
    {

        try {

            $page = ($request->page) ? $request->page : 1;
            $limit = ($request->limit) ? $request->limit : 10;

            $users = User::limit($limit)->offset(($page - 1) * $limit)->get();

            $total_users = User::all()->count();

            $response = ['users' => $users, "total" => $total_users];

            return response($response, 200);
        } catch (\Exception $exception) {

            return response([
                'message' => 'Server Error.'
            ], 500);
        }
    }
    public function login(Request $request)
    {

        try {
            $rules = array(
                'email'    => ['required', 'email'],
                'password' => ['required'],
            );


            $validator = Validator::make($request->all(), $rules);


            if ($validator->fails()) {
                $errors = $validator->messages();

                return response(["errors" => $errors], 422);
            }

            $data = $validator->valid();


            if (!auth()->attempt($data)) {

                $errors = array('error' => ['wrong email or password']);

                return response([
                    "errors" => ["message" => "These credentials do not match our records."]
                ], 422);

                return $this->faildData('validation error', $errors, 403);
            }

            $token = auth()->user()->createToken('authToken')->plainTextToken;

            $user = User::where('id', Auth::user()->id)->first();


            $response = ['user' => $user, 'token' => $token];

            return response($response, 201);
        } catch (\Exception $exception) {

            return response([
                'message' => 'Server Error.'
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {

            $rules = array(
                'name'                  => ['required'],
                'password'              => ['required', 'confirmed', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'],
                'email'                 => ['required', 'email', 'max:255', 'unique:users,email'],
                'password_confirmation' => ['required'],
            );

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errors = $validator->messages();

                return response(["errors" => $errors], 422);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            Auth::login($user);

            $token = auth()->user()->createToken('authToken')->plainTextToken;

            $response = ['user' => $user, 'token' => $token];

            return response($response, 201);
        } catch (\Exception $exception) {

            return response([
                'message' => 'Server Error.'
            ], 500);
        }
    }

    public function logout(Request $request)
    {

        $user = User::find(Auth::user()->id);
        $user->tokens()->delete();
        return response([
            'message' => 'Logged out successfully.'
        ], 200);
    }

    public function show(User $user)
    {
        try {

            if (!$user) {
                return response([
                    'message' => 'User not found.'
                ], 404);
            } else {

                return response([
                    'status' => true,
                    'user' => $user
                ], 200);
            }
        } catch (\Exception $exception) {

            return response([
                'message' => 'Server Error.'
            ], 500);
        }
    }

    public function update(Request $request, User $user)
    {
        try {

            $rules = array(
                'name' => ['required'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            );

            if (isset($request->password) && !empty($request->password)) {
                $rules['password'] = ['required', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'];
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errors = $validator->messages();

                return response(["errors" => $errors], 422);
            }


            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            $response = ['user' => $user, 'message' => "User Updated Successfully.", "status" => true];

            return response($response, 201);
        } catch (\Exception $exception) {

            return response([
                'message' => 'Server Error.'
            ], 500);
        }
    }

    public function destroy(User $user)
    {
        try {

            if (Auth::user()->id == $user->id) {
                return response([
                    'message' => "You can't delete your account."
                ], 400);
            }
            if (!$user) {
                return response([
                    'message' => 'User not found.'
                ], 404);
            } else {
                $user->delete();

                return response([
                    'status'  => true,
                    'message' => 'Deleted User Successfully.'
                ], 200);
            }
        } catch (\Exception $exception) {

            return response([
                'message' => 'Server Error.'
            ], 500);
        }
    }

    public function forget_password(Request $request)
    {
        try {
            $rules = array(
                'email'    => ['required', 'email'],
            );

            $validator = Validator::make($request->all(), $rules);


            if ($validator->fails()) {
                $errors = $validator->messages();

                return response(["errors" => $errors], 422);
            }

            $user = User::where('email', $request->email)->first();

            if ($user) {
                $new_password = $this->randomPassword();
                $user->update([
                    'password' => Hash::make($new_password)
                ]);
                $send_email = Mail::send([], [], function ($message) use ($user, $new_password) {
                    $message->to($user->email, 'Tutorials Point')->subject('SAGER DRONE - Reset Password')
                        ->setBody('Hi ' . $user->name . '!<br/> Your new passowrd is: ' . $new_password . '<br/> Please change it after login', 'text/html');
                    $message->from(env("MAIL_USERNAME", "info@fattouh.me"), 'SAGER DRONE');
                });

                return response([
                    'status'  => true,
                    'message' => 'See your email for new password.'
                ], 200);
            } else {
                return response([
                    'message' => 'User not found.'
                ], 404);
            }
        } catch (\Exception $exception) {
            return response([
                'message' => "Server Error."
            ], 500);
        }
    }

    public function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
}
