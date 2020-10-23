<?php

namespace App\Http\Controllers\API\v1\Permission;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;

use Carbon\Carbon;
use App\Permission;
use App\PermissionModule;
use App\Repositories\Permission\IPermissionRepository;

class PermissionController extends ApiController {

    private $permissionRepository;

    public function __construct(IPermissionRepository $iPermissionRepository) {
        $this->middleware('auth:api');
        $this->permissionRepository = $iPermissionRepository;
    }

    public function list(Request $request) {
        // $this->authorize('viewAny', Announcement::class);
        $permissions = $this->permissionRepository->list(true);
        return $this->responseWithData(200, $permissions);
    }
}
