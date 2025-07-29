<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Users\UpdateUserRequest;
use App\Http\Requests\Admin\Users\StoreUserRequest;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Route;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $this->indexService->limitPerPage($request->query('perPage', 10));
        $page = $this->indexService->checkPageIfNull($request->query('page', 1));
        $search = $this->indexService->checkIfSearchEmpty($request->query('search'));

        $users = User::latest();

        if ($search) {
            $users->where(function($query) use ($search) {
                $query->where('id', $search)
                      ->orWhere('firstname', 'like', '%' . $search . '%')
                      ->orWhere('lastname', 'like', '%' . $search . '%')
                      ->orWhere('username', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $users = $users->paginate($perPage, ['*'], 'page', $page);

        if ($request->expectsJson() || $request->hasHeader('X-Requested-With')) {
            return response()->json([
                'users' => $users->items(),
                'pagination' => $this->indexService->handlePagination($users)
            ]);
        }

        return inertia('Admin/Users/Index');
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::get();

        return inertia('Admin/Users/Create', compact('roles'));
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());
        $user->roles()->syncWithoutDetaching($request->input('roles'));
    
        if($request->has('file')){
            $file = $this->fileService->upload($request->file('file'), 'App\\Models\\User', $user->id);
        }

        return inertia('Admin/Users/Index', [
            'success' => 'User created successfully!'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {    
        $user->load('roles');
        return inertia('Admin/Users/Show', compact('user'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::get();

        return inertia('Admin/Users/Edit', compact('user', 'roles'));
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(User $user, UpdateUserRequest $request)
    {
        if($request->get('password') != null){
            $user->update([
                'password' => $request->get('password'),
            ]);
        }

        $user->update([
            'firstname' => $request->firstname,
            'lastname' => $request->lastname,
            'username' => $request->username,
            'email' => $request->email,
        ]);

        $user->roles()->sync($request->input('roles'));

        if($request->has('file')){
            if($user->file) $user->file->detach();

            $file = $this->fileService->upload($request->file('file'), 'App\\Models\\User', $user->id);
        }
    
        return inertia('Admin/Users/Index', [
            'success' => 'User updated successfully!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
                        ->with('success','User deleted successfully');
    }
}
