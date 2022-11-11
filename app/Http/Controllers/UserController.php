<?php

namespace App\Http\Controllers;

use App\Events\UserEventCreated;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * @var \App\Models\User|null
     */
    private $userModel = null;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    public function index()
    {
        $user =  $this->userModel->get();

        return response()->json([
            'users' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'sometimes|mimes:jpg,png',
            'email' => 'email|unique:users',
            'password' => 'required',

        ]);
        $errors = $validator->errors();
        // if($request->has('image')){

        if ($validator->fails()) {
            return response()->json([
                'message' => $errors,
            ]);
        }

        $image = null;

        if ($request->hasFile('image')) {
            $image = $request->file('image')->store('users', 'public');
        }

        $user = $this->userModel->create([
            'name' =>   $request->name,
            'email' => $request->email,
            'email_verified_at' => now(),
            'password' => Hash::make($request->password),
            "image" => $image,
        ]);

        if ($request->hasFile('image')) {
            $user->image =  $this->userModel->image($user->image);
        }

        $UserCreated=["user"=>$user->name];
        event(new UserEventCreated($UserCreated));



        return response()->json([
            'users' => $user,
        ]);

        // }

    }

    public function show(Request $request)
    {
        $user = $request->user();
        $user->image =  $this->userModel->image($user->image);

        return response()->json([
            'user' => $user,
        ]);
    }

    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'image' => 'sometimes|mimes:jpg,png',
            'email' => [
                'email', Rule::unique('users')->ignore($id),
            ],
        ]);
        $errors = $validator->errors();
        if ($validator->fails()) {
            return response()->json([
                'users' => $errors,
            ]);
        } else {
            $user = $this->userModel->find($id);
            if ($request->hasFile('image')) {
                $imagePath =  Storage::disk('public')->exists($user->image);
                if ($imagePath == true) {
                    Storage::delete('public/' . $user->image);
                }
                $image = request()->file('image')->store('users', 'public');
                $data['image'] = $image;
                $user->email = $request->email;
                $user->image =  $this->userModel->image($user->image);
            } else {
                $image = '';
                $user->email = $request->email;
                $data['image'] = $image;
            }

            $user->update($data);
        }
        return response()->json([
            'record updated'
        ], 200);
    }

    public function destroy($id)
    {
        $user =  $this->userModel->find($id);
        unlink($user->image);
        $user->delete();
        return response()->json([
            'message' => $user->image
        ]);
        //we can also send 204 (no content) if we are not returning anything
        // return response()->json();
    }

    function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => ['These credentials do not match our records.']
            ], 404);
        }

        $token = $user->createToken('my-app-token')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];


        return response()->json([
            'response' => $response,
        ], 201);
    }

    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request...
        $request->user()->currentAccessToken()->delete();
        $response = [
            'message' => "log out successfully..."
        ];
        return response()->json([
            'response' => $response,
        ], 201);
    }
}
