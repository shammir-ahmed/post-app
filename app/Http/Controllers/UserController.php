<?php

namespace App\Http\Controllers;

use App\Models\Meta;
use App\Models\User;
use App\Jobs\ResizedImage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Resources\Json\JsonResource;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;

class UserController extends Controller
{
    public function __construct()
    {
    }

    /**
     * get all property variables
     * @return \Illuminate\Http\Resources
     */

    public function getVariables()
    {
        $data = User::getVariables();

        return response()->json($data);
    }

    public function index(Request $request)
    {
        $authuser = $request->user();

        if (!($authuser->isAdministrator() || $authuser->hasPermissionTo('user list'))) {
            return response()->json(["message"=>"Unauthorized"], 403);
        }

        if ($request->has('offset') && $request->has('limit')) {
            $users = User::orderBy('id', 'desc')->skip($request->offset)->limit($request->limit)->get();
            return UserResource::collection($users);
        }

        $role = $request->role;

        $paginate = $request->paginate? intval($request->paginate) : 10;
        $status = $request->status?: 'active';

        $users = User::orderBy('id', 'desc');

        if (!$authuser->isAdministrator()) {
            $users = $users->where('broker_id', $authuser->id)->orWhere('broker_id', 0);
        }

        if ($status !== 'all') {
            $users = $users->where('status', $status);
        }


        if ($role) {
            $users = $users->whereHas('roles', function ($q) use ($role) {
                return $q->where('id', $role);
            });
        }

        if ($request->has('s') && strlen($request->s) > 0) {
            $users = $users->whereRaw("MATCH (first_name,last_name,email,phone) AGAINST (? IN BOOLEAN MODE)", $request->s);
        }

        if ($request->has('ids')) {
            $users->whereIn('id', $request->ids);
        }

        if ($paginate <= 0) {
            $users = $users->get();
        } else {
            $users = $users->paginate($paginate);
        }

        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $authuser = $request->user();

        if (!($authuser->isAdministrator() || $authuser->hasPermissionTo('user create'))) {
            return response()->json(["message"=>"Unauthorized"], 403);
        }

        $passwordResetToken = false;

        // Retrieve the validated input data...
        $this->validate($request, [
            'first_name'      => 'required',
            'email'           => 'required|email|unique:users,email',
            'username'        => 'required|nullable|unique:users,username',
            'password'        => 'nullable|string|min:6',
        ]);

        $data = $request->all();

        // if ($authuser->isAdministrator() && in_array($data['user_type'], ['buyer', 'owner', 'both'])) {
        //     $data['broker_id'] = 0;
        // } else {
        //     $data['broker_id'] = $authuser->id;
        // }

        if (empty($data['username'])) {
            $data['username'] = generate_username($data);
        }

        if ($request->password) {
            $data['password'] = Hash::make($data['password']);
        } else {
            $data['password'] = Hash::make(Str::random(12));
            $passwordResetToken = true;
        }

        $user = User::create($data);

        // $roles = [];

        // if ($data['user_type'] == 'both') {
        //     $roles = ['buyer', 'owner'];
        // } elseif (in_array($data['user_type'], ['buyer', 'owner'])) {
        //     $roles = [$data['user_type']];
        // }

        // $user->assignRole($roles);

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        if ($request->has('scopes')) {
            $user->syncPermissions($request->scopes);
        }

        if ($passwordResetToken) {
            $meta = $this->generateToken($user->id);
        }

        // Redis::publish(
        //     config('app.channel_prefix').'.created',
        //     json_encode([
        //         'user' => $authuser->toSimple(),
        //         'owners' => [
        //             'ruser' => $user->toSimple(),
        //         ],
        //         'user_type' => $roles,
        //         'verify_url' => $passwordResetToken ? config('app.front_end')."/auth/password-reset?token=".$meta->value : null,
        //     ])
        // );

        return new UserResource($user);
    }


    /**
     * generate email varification Token
     *
     * @param string $email
     * @param integer $user_id
     * @return Meta
     */
    protected function generateToken($user_id = 0)
    {
        $meta = Meta::firstOrNew([
            'user_id' => $user_id,
            'key'     => 'password_reset_token'
        ]);

        $meta->value = Str::random(64);
        $meta->is_hidden = true;

        $meta->save();

        return $meta;
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserByEmail(Request $request)
    {
        $authuser = $request->user();

        if (!($authuser->isAdministrator() || $authuser->hasPermissionTo('user view'))) {
            return response()->json(["message"=>"Unauthorized"], 403);
        }

        $user = User::where('email', $request->email)->first();

        Cache::rememberForever("users.$user->id.payload", function () use ($user) {
            return $user->only(['id', 'username', 'full_name', 'email']);
        });

        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $user)
    {
        $authuser = $request->user();

        if (!($authuser->isAdministrator() || $authuser->hasPermissionTo('user view'))) {
            return response()->json(["message"=>"Unauthorized"], 403);
        }

        $user = User::with(['meta' => function ($q) {
            return $q->where('is_hidden', false);
        }])->findOrFail($user);

        Cache::rememberForever("users.$user->id.payload", function () use ($user) {
            return $user->only(['id', 'username', 'full_name', 'email']);
        });

        return new UserResource($user);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return Illuminate\Http\Resources\Json\JsonResource
     */
    public function update(Request $request, $user): \Illuminate\Http\Resources\Json\JsonResource
    {
        $authuser = $request->user();

        if ($authuser->id != $user) {
            if (!($authuser->isAdministrator() || $authuser->hasPermissionTo('user update'))) {
                return response()->json(["message"=>"Unauthorized"], 403);
            }
        }

        $this->validate($request, [
            'email'           => "email|unique:users,email,$user",
            'username'        => "unique:users,username,$user",
            'password'        => 'string|min:6',
        ]);

        $user = User::findOrFail($user);

        $data = $request->only([
            'first_name',
            'last_name',
            'phone',
            'gender',
            'status',
            'city',
            'country',
            'zip',
            'timezone'
        ]);

        if ($request->has('password') && strlen($request->password) > 0) {
            $request->password = Hash::make($request->password);
        }

        $user->fill($data);

        $user->save();

        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        if ($request->has('scopes')) {
            $user->syncPermissions($request->scopes);
        }


        // Redis::publish(
        //     config('app.channel_prefix').'.update',
        //     json_encode([
        //         'user' => $authuser->toSimple(),
        //         'owners' => [
        //             'ruser' => $user->toSimple(),
        //         ],
        //     ])
        // );

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $user)
    {
        $authuser = $request->user();

        if (!($authuser->isAdministrator() || $authuser->hasPermissionTo('user delete'))) {
            return response()->json(["message"=>"Unauthorized"], 403);
        }

        User::where('id', $user)->delete();

        if (Cache::has("users.$user.role-n-permission")) {
            Cache::forget("users.$user.role-n-permission");
        }

        return response()->json(['message' => 'User Deletion Successful!'], 200);
    }

    /**
     * Active specified resource.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function active(Request $request, $user)
    {
        $authuser = $request->user();

        if (!($authuser->isAdministrator() || $authuser->hasPermissionTo('user active'))) {
            return response()->json(["message"=>"Unauthorized"], 403);
        }

        $user = User::findOrFail($user);
        $user->status = User::STATUS_ACTIVE;
        $user->save();

        // Redis::publish(
        //     config('app.channel_prefix').'.active',
        //     json_encode([
        //         'user' => $authuser->toSimple(),
        //         'owners' => [
        //             'ruser' => $user->toSimple(),
        //         ],
        //     ])
        // );

        return new UserResource($user);
    }

    /**
     * Suspend specified resource.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\Response
     */
    public function suspend(Request $request, $user)
    {
        $authuser = $request->user();

        if (!($authuser->isAdministrator() || $authuser->hasPermissionTo('user suspend'))) {
            return response()->json(["message"=>"Unauthorized"], 403);
        }

        $user = User::findOrFail($user);
        $user->status = User::STATUS_SUSPENDED;
        $user->save();

        if (Cache::has("users.$user.role-n-permission")) {
            Cache::forget("users.$user.role-n-permission");
        }

        // Redis::publish(
        //     config('app.channel_prefix').'.suspend',
        //     json_encode([
        //         'user' => $authuser->toSimple(),
        //         'owners' => [
        //             'ruser' => $user->toSimple(),
        //         ],
        //     ])
        // );

        return new UserResource($user);
    }

    public function assignRole(Request $request, $user)
    {
        $authuser = $request->user();

        if (!($authuser->isAdministrator() || $authuser->hasPermissionTo('user role assign'))) {
            return response()->json(["message"=>"Unauthorized"], 403);
        }

        $user = User::findOrFail($user);

        $roles = $request->get('roles', []);
        if (gettype($roles) === 'string') {
            $roles = strpos($roles, ',') ? explode(',', $roles): [$roles];
        }

        $user->syncRoles($roles);

        if (Cache::has("users.$user.role-n-permission")) {
            Cache::forget("users.$user.role-n-permission");

            Cache::remember("users.$user.role-n-permission", config('cache.ttl.jwt'), function () use ($user) {
                return [
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()
                ];
            });
        }

        // Redis::publish(
        //     config('app.channel_prefix').'.role.assign',
        //     json_encode([
        //         'user' => $authuser->toSimple(),
        //         'owners' => [
        //             'ruser' => $user->toSimple(),
        //         ],
        //         'roles' => $user->all_roles,

        //     ])
        // );

        return new UserResource($user);
    }

    public function assignPermission(Request $request, $user)
    {
        $authuser = $request->user();

        if (!($authuser->isAdministrator() || $authuser->hasPermissionTo('user permission assign'))) {
            return response()->json(["message"=>"Unauthorized"], 403);
        }

        $user = User::findOrFail($user);

        $permissions = $request->get('permissions', []);

        if (gettype($permissions) === 'string') {
            $permissions = strpos($permissions, ',') ? explode(',', $permissions): [$permissions];
        }

        $user->syncPermissions($permissions);

        if (Cache::has("users.$user.role-n-permission")) {
            Cache::forget("users.$user.role-n-permission");

            Cache::remember("users.$user.role-n-permission", config('cache.ttl.jwt'), function () use ($user) {
                return [
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()
                ];
            });
        }

        // Redis::publish(
        //     config('app.channel_prefix').'.permission.assign',
        //     json_encode([
        //         'user' => $authuser->toSimple(),
        //         'owners' => [
        //             'ruser' => $user->toSimple(),
        //         ],
        //         'scopes' => $user->scopes,
        //     ])
        // );

        return new UserResource($user);
    }

    public function getRoleNPermissions($user)
    {
        $user = User::findOrFail($user);

        $roleNPermissions = [
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()
        ];

        return response()->json($roleNPermissions);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\UserRequest $request
    *
     * @return \Illuminate\Http\Response
     */
    public function avatarUpload(Request $request)
    {
        $request->input('file_name', $request->get('dzuuid'));
        $inputs = $request->all();
        $inputs['meta']['sizes'] = $this->getDefaultSizes();
        $inputs['meta']['permissions'] = $this->getFilePermissions($request);


        $chunkupload = true;
        if ($chunkupload) {
            // create the file receiver
            $receiver = new FileReceiver('file', $request, HandlerFactory::classFromRequest($request));

            // check if the upload is success, throw exception or return response you need
            if ($receiver->isUploaded() === false) {
                throw new UploadMissingFileException();
            }

            // receive the file
            $save = $receiver->receive();

            // check if the upload has finished (in chunk mode it will send smaller files)
            if ($save->isFinished()) {
                // save the file and return any response you need, current example uses `move` function. If you are
                // not using move, you need to manually delete the file by unlink($save->getFile()->getPathname())
                $filedata = $this->getFileData($save->getFile(), $inputs);
                dd($filedata);
                // $fileEntry = File::create($filedata);

                if (config('filesystems.default') === 'local') {
                    $this->storeLocalUpload($fileEntry, $save->getFile());
                }
                if (config('filesystems.default') === 's3') {
                    $this->storeCloudUpload($fileEntry, $save->getFile());
                }

                $cropScope = $request->has('crop_scope') && strlen($request->crop_scope) > 0 ? $request->crop_scope : [];

                if (gettype($cropScope) !== 'array') {
                    $cropScope = strpos($cropScope, ',') ? explode(',', $cropScope) : [$cropScope];
                }

                foreach (['60x60', '100x100', '300x300'] as $size) {
                    if (!in_array($size, $cropScope)) {
                        $cropScope[] = $size;
                    }
                }

                ResizedImage::dispatch($cropScope, $fileEntry);
                // $this->file->resizeImage($fileEntry, $save->getFile());


                // UploadToCloud::dispatch($fileEntry)->delay(now()->addMinutes(10)); // fire resize event and uplad to cloud
                $resource = new JsonResource($fileEntry);

                return $resource;
            }

            // we are in chunk mode, lets send the current progress
            /** @var AbstractHandler $handler */
            $handler = $save->handler();

            return response()->json([
                'done' => $handler->getPercentageDone(),
                'status' => true,
            ]);
        }
    }
}
