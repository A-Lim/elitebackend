<?php
namespace App\Repositories\UserGroup;

use DB;
use App\UserGroup;
use Carbon\Carbon;

class UserGroupRepository implements UserGroupRepositoryInterface {
    /**
     * {@inheritdoc}
     */
    public function codeExists($code, $userGroupId = null) {
        $conditions = [['code', '=', $code]];
        if ($userGroupId != null)
            array_push($conditions, ['id', '<>', $userGroupId]);

        return UserGroup::where($conditions)->exists();
    }

     /**
     * {@inheritdoc}
     */
    public function list($data, $paginate = false) {
        $query = UserGroup::buildQuery($data)
            ->withCount('users');

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
        return UserGroup::with(['users', 'permissions'])->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function create($data) {
        $data['deleted_at'] = null;
        $data['created_by'] = auth()->id();
        $userGroup = UserGroup::withTrashed()->updateOrCreate(
            ['code' => $data['code']],
            $data
        );

        // if isAdmin, dont save permissions, cause it's gonna be full access
        // save permissions if not admin
        if ($data['is_admin'] == false && !empty($data['permissions'])) 
            $userGroup->givePermissions($data['permissions']);
        
        if (!empty($data['userIds']))
            $userGroup->users()->sync($data['userIds']);
        
        return UserGroup::with('permissions')->where('id', $userGroup->id)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function update(UserGroup $userGroup, $data) {
        $data['updated_by'] = auth()->id();

        if (!empty($data['code']))
            unset($data['code']);

        // if isAdmin, dont save permissions, cause it's gonna be full access
        // save permissions if not admin
        if ($data['is_admin'] == false && !empty($data['permissions'])) 
            $userGroup->givePermissions($data['permissions']);

        if (!empty($data['userIds']))
            $userGroup->users()->sync($data['userIds']);

        $userGroup->fill($data);
        $userGroup->save();
        return $userGroup;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Usergroup $userGroup, $forceDelete = false) {
        if ($forceDelete) {
            $userGroup->forceDelete();
        } else {
            $data['updated_by'] = auth()->id();
            $data['deleted_at'] = Carbon::now()->format('Y-m-d H:i:s');
            $userGroup->fill($data);
            $userGroup->save();
        }
    }
}