<?php

namespace App\Http\Controllers\API\v1\SystemSetting;

use Illuminate\Http\Request;
use App\SystemSetting;
use App\Http\Controllers\ApiController;
use App\Http\Requests\SystemSetting\UpdateRequest;
use App\Repositories\SystemSetting\SystemSettingRepositoryInterface;

class SystemSettingController extends ApiController {

    private $systemSettingRepository;

    public function __construct(SystemSettingRepositoryInterface $systemSettingRepositoryInterface) {
        $this->middleware('auth:api')
            ->except(['allowPublicRegistration']);
        $this->systemSettingRepository = $systemSettingRepositoryInterface;
    }

    public function list(Request $request) {
        $this->authorize('viewAny', SystemSetting::class);
        $systemSettings = $this->systemSettingRepository->list();
        return $this->responseWithData(200, $systemSettings);
    }

    public function update(UpdateRequest $request) {
        $this->authorize('update', SystemSetting::class);
        $this->systemSettingRepository->update($request->all());
        return $this->responseWithMessage(200, 'System settings updated.');
    }

    public function allowPublicRegistration(Request $request) {
        $allowed = (bool) $this->systemSettingRepository->findByCode('allow_public_registration')->value;
        return $this->responseWithData(200, $allowed);
    }
}
