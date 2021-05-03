<?php

namespace App\Http\Controllers\API\v1\UserGroup;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;

use App\User;
use App\UserGroup;
use App\Repositories\UserGroup\IUserGroupRepository;

use App\Http\Requests\UserGroup\CreateRequest;
use App\Http\Requests\UserGroup\UpdateRequest;
use App\Http\Requests\UserGroup\AddUsersRequest;
use App\Http\Requests\UserGroup\CodeExistsRequest;

class UserGroupController extends ApiController {

    private $userGroupRepository;

    public function __construct(IUserGroupRepository $iUserGroupRepository) {
        $this->middleware('auth:api');
        $this->userGroupRepository = $iUserGroupRepository;
    }

    public function exists(CodeExistsRequest $request) {
        $exists = $this->userGroupRepository->codeExists($request->code, $request->userGroupId);
        return $this->responseWithData(200, $exists);
    }
    
    public function list(Request $request) {
        if ($request->type != 'formcontrol')
            $this->authorize('viewAny', UserGroup::class);
        
        $userGroups = $this->userGroupRepository->list($request->all(), true);
        return $this->responseWithData(200, $userGroups);
    }

    public function listUsers(Request $request, UserGroup $userGroup) {
        if ($request->type != 'formcontrol')
            $this->authorize('viewAny', UserGroup::class);
        
        $users = $this->userGroupRepository->listUsers($userGroup, $request->all(), true);
        return $this->responseWithData(200, $users);
    }

    public function listNotUsers(Request $request, UserGroup $userGroup) {
        if ($request->type != 'formcontrol')
            $this->authorize('viewAny', UserGroup::class);
        
        $users = $this->userGroupRepository->listNotUsers($userGroup, $request->all(), true);
        return $this->responseWithData(200, $users);
    }

    public function create(CreateRequest $request) {
        $this->authorize('create', UserGroup::class);
        $userGroup = $this->userGroupRepository->create($request->all());
        return $this->responseWithMessageAndData(201, $userGroup, 'User group created.');
    }

    public function details(UserGroup $userGroup) {
        $this->authorize('view', $userGroup);
        $userGroup = $this->userGroupRepository->find($userGroup->id);
        return $this->responseWithData(200, $userGroup); 
    }

    public function addUsers(AddUsersRequest $request, UserGroup $userGroup) {
        $this->authorize('update', $userGroup);
        $this->userGroupRepository->addUsers($userGroup, $request->all());
        return $this->responseWithMessage(200, 'Users added.'); 
    }

    public function removeUser(Request $request, UserGroup $userGroup, User $user) {
        $this->authorize('update', $userGroup);
        $this->userGroupRepository->removeUser($userGroup, $user);
        return $this->responseWithMessage(200, 'User removed.'); 
    }

    public function update(UpdateRequest $request, UserGroup $userGroup) {
        $this->authorize('update', $userGroup);
        $userGroup = $this->userGroupRepository->update($userGroup, $request->all());
        return $this->responseWithMessageAndData(200, $userGroup, 'User group updated.'); 
    }

    public function delete(UserGroup $userGroup) {
        $this->authorize('delete', $userGroup);
        $this->userGroupRepository->delete($userGroup);
        return $this->responseWithMessage(200, 'User group deleted.');
    }
}
