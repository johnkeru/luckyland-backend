<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function validateData(array $rules)
    {
        $responseData['success'] = true;
        $validator = Validator::make(request()->all(), $rules);
        if ($validator->fails()) {
            $responseData['success'] = false;
            $responseData['message'] = 'Validation failed';
            $responseData['errors'] = [];
            foreach ($validator->errors()->messages() as $field => $messages) {
                $responseData['errors'][] = [
                    'field' => $field,
                    'message' => $messages[0]
                ];
            }
            return response()->json($responseData); // Return validation errors as JSON
        }
    }

    public function user()
    {
        $user = User::with(['roles', 'address'])->find(request()->user()->id);
        return response()->json([
            'user' => Auth::user(),
            'data' => $user
        ]);
        return $user;
    }

    public function login(Request $request)
    {
        try {
            // Validate user input
            $this->validateData([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            // Attempt to authenticate user
            if (!Auth::attempt($request->only(['email', 'password']))) {
                // Check if the email exists in the database
                $user = User::where('email', $request->email)->first();

                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Email does not exist.',
                        'field' => 'email'
                    ], 401);
                }

                // Check if the password is incorrect
                if (!Hash::check($request->password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Password is incorrect.',
                        'field' => 'password'
                    ], 401);
                }

                // If none of the above conditions are met, return a generic message
                return response()->json([
                    'success' => false,
                    'message' => 'Email and password do not match our records.'
                ], 401);
            }

            // Retrieve authenticated user
            $user = User::where('email', $request->email)->first();

            // Return successful login response
            return response()->json([
                'success' => true,
                'message' => 'User logged in successfully',
                'data' => [
                    'token' => $user->createToken("API TOKEN")->plainTextToken
                ]
            ], 200);
        } catch (\Throwable $th) {
            // Handle any exceptions
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Revoke all tokens associated with the authenticated user
            $request->user()->tokens()->delete();

            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully',
            ], 200);
        } catch (\Exception $e) {
            // Handle any exceptions
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while logging out.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        $this->validateData([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json([
                'success' => true,
                'message' => __($status),
                'field' => "email",
            ], 200)
            : response()->json([
                'success' => false,
                'message' => __($status),
                'field' => "email",
            ], 400);
    }

    public function resetPassword(Request $request)
    {
        $this->validateData([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json([
                'success' => true,
                'message' => __($status)
            ], 200)
            : response()->json([
                'success' => false,
                'message' => __($status)
            ], 400);
    }
}
