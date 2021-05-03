<?php
namespace App\Repositories\UserGroup;

use App\UserGroup;

interface IUserGroupRepository
{
    /**
     * Check if code exists
     */
    public function codeExists($code, $userGroupId = null);

    /**
     * List usergroup
     * 
     * @param array $query
     * @param boolean $paginate = false
     * @return [UserGroup]
     */
    public function list($query, $paginate = false);

    /**
     * List users that belong to usergroup
     * 
     * @param array $query
     * @param boolean $paginate = false
     * @return [User]
     */
    public function listUsers(UserGroup $userGroup, $data, $paginate = false);

    /**
     * List users that does not belong to usergroup
     * 
     * @param array $query
     * @param boolean $paginate = false
     * @return [User]
     */
    public function listNotUsers(UserGroup $userGroup, $data, $paginate = false);

    /**
     * List users that belong to a usergroup from usergroup code
     * 
     * @param string $code
     * @return [User]
     */
    // public function listActiveUsersFromCode($code);

    /**
     * Find usergroup from id
     * 
     * @param integer $id
     * @return UserGroup
     */
    public function find($id);

    
    /**
     * Creates a usergroup
     * 
     * @param array $data
     * @return UserGroup
     */
    public function create($data);

    /**
     * Updates a usergroup
     * 
     * @param UserGroup $userGroup
     * @param array $data
     * @return UserGroup
     */
    public function update(UserGroup $userGroup, $data);

    /**
     * Deletes a usergroup
     * 
     * @param UserGroup $userGroup
     * @param bool $forceDelete
     * @return void
     */
    public function delete(UserGroup $userGroup, $forceDelete = false);

}