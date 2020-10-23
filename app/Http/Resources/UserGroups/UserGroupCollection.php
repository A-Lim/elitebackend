<?php

namespace App\Http\Resources\UserGroups;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserGroupCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        return UserGroupResource::collection($this->collection);
    }
}
