<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
    * Create User
    * @param Request &request
    * @return User
    */
    public function createUser(Request $request)
    {

        try {
            $validateUser = Validator::make($request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'error' => $validateUser->errors()
                ], 401);
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User Created Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }


    }

    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(),
            [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            if(!Auth::attempt($request->only(['email', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function recoveryUser(Request $request)
    {
        try {
            # check if email exists in database
            $user = User::where('email', $request->email)->first();

            if($user != null){
                # send email to user

                # CALLING MAIL FUNCTION

                return response()->json([
                    'status' => true,
                    'message' => 'Email Sent Successfully',
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => 'Email does not exists in our record',
            ], 401);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function editUserPassword(Request $request)
    {
        try {

            $user = User::where('id', auth('sanctum')->user()->id)->first()->setVisible(['password']);;

            if(Hash::check($request->old_password, $user->password)){
                $user->password = Hash::make($request->new_password);
                $user->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Password Changed Successfully',
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => 'Old Password do not match!',
            ], 401);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    # function to edit user profile (name, email)
    public function userEditProfile(Request $request)
    {
        try {

            $user = User::where('id', auth('sanctum')->user()->id)->first();

            if($request->name != null){
                $user->name = $request->name;
            }
            if($request->email != null && $request->email != $user->email){

                # check if email is valid
                $validateEmail = Validator::make($request->all(),
                [
                    'email' => 'required|email|unique:users,email',
                ]);

                if($validateEmail->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'errors' => $validateEmail->errors()
                    ], 401);
                }

                $user->email = $request->email;
            }

            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'User Profile Updated Successfully',
                'user' => $user
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function logoutUser(Request $request)
    {
        try {
            auth('sanctum')->user()->currentAccessToken()->delete();
            return response()->json([
                'status' => true,
                'message' => 'User Logged Out Successfully',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
