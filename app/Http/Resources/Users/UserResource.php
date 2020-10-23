<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserGroups\UserGroupCollection;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        // dd(UserGroupsResource::collection($this->usergroups));
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'email_verified_at' => $this->email_verified_at,
            'status' => $this->status,
            'usergroups' => new UserGroupCollection($this->usergroups)
        ];
    }
}
