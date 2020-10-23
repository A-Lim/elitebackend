<?php
namespace App\Repositories\User;

use DB;
use App\User;
use App\UserGroup;
use App\Permission;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;

use App\Helpers\ImageProcessor;

class UserRepository implements UserRepositoryInterface {

    public function permissions(User $user) {
        $userGroups = UserGroup::whereHas('users', function($query) use ($user) {
            $query->where('user_id', '=', $user->id);
        })->get();
        
        
        $isAdmins = $userGroups->pluck('is_admin')->all();
        // check if there is an admin usergroups among the user's usergroup
        $hasAdmin = in_array(true, $isAdmins);

        // is has admin usergroup, return all permissions
        if ($hasAdmin)
            return Permission::pluck('code');

        $userGroupIds = $userGroups->pluck('id')->all();
        $permissions = Permission::whereHas('userGroups', function ($query) use ($userGroupIds) {
            $query->whereIn('usergroup_id', $userGroupIds);
        })->get()->pluck('code')->all();

        return $permissions;
    }
    
    /**
     * {@inheritdoc}
     */
    public function list($data, $paginate = false) {
        $query = User::buildQuery($data);

        if (isset($data['id']) && is_array($data['id'])) {
            $ids = implode(',', $data['id']);
            $query->orderByRaw(DB::raw("FIELD(id,".$ids.") DESC"));
        }

        if ($paginate) {
            $limit = isset($data['limit']) ? $data['limit'] : 10;
            return $query->paginate($limit);
        }
        
        return $query->get();
    }

    /**
     * {@inheritdoc}
     */
    public function find($id) {
        return User::find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findWithUserGroups($id) {
        return User::with('usergroups')->where('id', $id)->firstOrFail();
    }

    /**
     * {@inheritdoc}
     */
    public function searchForOne($params) {
        return User::where($params)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function create($data) {
        $data['password'] = Hash::make($data['password']);
        return User::create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(User $user, $data) {
        if (!empty($data['password']))
            $data['password'] = Hash::make($data['password']);

        if (!empty($data['usergroups']))
            $user->userGroups()->sync($data['usergroups']);

        $user->fill($data);
        $user->save();
        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function resetPassword(User $user, $password) {
        $user->password = $password;
        $user->setRememberToken(Str::random(60));
        $user->save();
    }

    /**
     * {@inheritdoc}
     */
    public function saveAvatar(User $user, UploadedFile $file) {
        $imagePaths = [];

        $image = new ImageProcessor($file, 'users/avatar', $user->id);
        $imagePaths['normal'] = $image->fit(400, 400)->save();

        $image = new ImageProcessor($file, 'users/avatar', $user->id);
        $imagePaths['placeholder'] = $image->placeholder(400, 400)->save();

        $image = new ImageProcessor($file, 'users/avatar', $user->id);
        $imagePaths['thumbnail'] = $image->thumbnail()->save();

        $user->avatar = json_encode($imagePaths);
        $user->save();

        return $user->avatar;
    }

    public function randomizePassword(User $user) {
        $random_password = Str::random(8);
        $user->password = Hash::make($random_password);
        $user->setRememberToken(Str::random(60));
        $user->save();

        return $random_password;
    }
}