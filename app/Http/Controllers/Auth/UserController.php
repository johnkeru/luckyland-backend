<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddEmployeeRequest;
use App\Http\Requests\AddRegularEmployeeRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Http\Responses\EmployeeIndexResponse;
use App\Mail\ResetLinkMail;
use App\Models\Address;
use App\Models\ForgotPasswordToken;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


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
            $user = User::with(['roles', 'address'])->where('email', $request->email)->first();

            // Return successful login response
            return response()->json([
                'success' => true,
                'message' => 'Welcome, ' . ucfirst($user->firstName) . '! You are now logged in.',
                'data' => [
                    'user' => $user,
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

    private function generateTokenLinkForReset($user)
    {
        $token = Str::random(60);
        $expiry = Carbon::now()->addHour();
        ForgotPasswordToken::create([
            'token' => $token,
            'expiry_time' => $expiry,
            'user_id' => $user->id,
        ]);
        $frontendURL = env('FRONTEND_URL');
        $link = URL::to("$frontendURL/password-reset/$token" . "?email=$user->email");
        return $link;
    }

    public function forgotPassword(Request $request)
    {
        $this->validateData([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
                'field' => 'email',
            ], 404);
        }
        $resetToken = $this->generateTokenLinkForReset($user);

        $emailContent = [
            'resetToken' => $resetToken,
            'email' => $user->eamil
        ];
        Mail::to($request->email)->send(new ResetLinkMail($emailContent));
        return response()->json([
            'success' => true,
            'message' => 'Password reset email sent successfully.',
        ], 200);
    }


    public function resetPassword(Request $request)
    {
        $this->validate($request, [
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $token = $request->token;
        $email = $request->email;
        $password = $request->password;

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
                'field' => 'email',
            ], 404);
        }

        $resetToken = ForgotPasswordToken::where('token', $token)
            ->where('user_id', $user->id)
            ->first();

        if (!$resetToken) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token.',
                'field' => 'token',
            ], 400);
        }

        if (Carbon::now()->gt($resetToken->expiry_time)) {
            // Token has expired
            return response()->json([
                'success' => false,
                'message' => 'Token has expired.',
                'field' => 'token',
            ], 400);
        }

        // Update user's password
        $user->password = Hash::make($password);
        $user->save();

        // Delete the used token
        $resetToken->delete();

        $user->tokens()->delete();

        // Logout the user
        Auth::logout();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successful.',
        ], 200);
    }

    public function index()
    {
        try {
            // SEARCH FILTERS
            // http://localhost:8000/api/users?search=anyvalue
            $search = request()->query('search');
            $firstName = request()->query('firstName');
            $role = request()->query('role');
            $address = request()->query('address');
            $status = request()->query('status') ?? 'active';
            $trash = request()->query('trash');

            $users = User::onlyActive($status)
                ->search($search)
                ->orderByFirstName($firstName)
                ->orderByAddress($address)
                ->latest('created_at')
                ->filterByRole($role) // causing the problem for regular
                ->withTrashcan($trash)
                ->whereDoesntHave('roles', function ($query) {
                    $query->where('roleName', 'Admin');
                })
                ->with(['roles', 'address'])
                ->paginate(8);

            return new EmployeeIndexResponse($users);
        } catch (\Exception $e) {
            // Log or handle the exception as needed
            return response()->json(['success' => false, 'message' => 'An error occured', 'error' => $e->getMessage()], 500);
        }
    }

    public function getRoles()
    {
        return Role::all();
    }

    public function updateImage()
    {
        $image = request()->input('image');
        $user = User::with(['roles', 'address'])->find(auth()->user()->id);
        $user->update(['image' => $image]);
        return response()->json(['success' => true, 'message' => 'Successfully updated.', 'data' => $user, 'image' => $image]);
    }

    public function addEmployee(AddEmployeeRequest $request)
    {
        try {
            $data = $request->validated();
            $address = new Address($data);

            $roles = $data['roles'];
            $newEmployee = User::create($data);
            $newEmployee->address()->save($address);
            $newEmployee->roles()->attach($roles);

            return response()->json(['success' => true, 'message' => 'Successfully added.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occured.', 'error' => $e->getMessage()], 500);
        }
    }

    public function addRegularEmployee(AddRegularEmployeeRequest $request)
    {
        try {
            $data = $request->validated();
            $address = new Address($data);
            $data['type'] = 'regular';
            $newEmployee = User::create($data);
            $newEmployee->address()->save($address);

            return response()->json(['success' => true, 'message' => 'Successfully added.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occured.', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateEmployee($id, UpdateEmployeeRequest $request)
    {
        try {
            $employee = User::with(['address', 'roles'])->findOrFail($id);
            $data = $request->validated();
            $employee->roles()->sync($data['roles']);
            if ($employee->address) {
                $employee->address->update($data);
            } else {
                $address = new Address($data);
                $employee->address()->save($address);
            }
            $employee->update($data);
            return response()->json(['success' => true, 'message' => 'Successfully updated.', 'data' => $employee]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occured.', 'error' => $e->getMessage()], 500);
        }
    }

    public function changePassword($id, ChangePasswordRequest $request)
    {
        try {
            $data = $request->validated();
            $user = User::with(['address', 'roles'])->findOrFail($id);

            if (!Hash::check($data['current_password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'field' => 'current_password',
                    'msg' => 'The provided current password is incorrect.',
                ], 400);
            }

            $user->update([
                'password' => Hash::make($data['new_password']),
            ]);

            return response()->json(['success' => true, 'message' => 'Password changed successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occured.', 'error' => $e->getMessage()], 500);
        }
    }

    public function softDeleteOrRestoreEmployee($id)
    {
        try {
            $employee = User::withTrashed()->find($id);

            if (!$employee) {
                return response()->json(['success' => false, 'message' => 'User not found.'], 404);
            }

            if ($employee->trashed()) {
                $employee->restore();
                return response()->json(['success' => true, 'message' => 'Successfully restored.']);
            }

            $employee->delete();
            return response()->json(['success' => true, 'message' => 'Successfully soft-deleted.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occured.', 'error' => $e->getMessage()], 500);
        }
    }
}
