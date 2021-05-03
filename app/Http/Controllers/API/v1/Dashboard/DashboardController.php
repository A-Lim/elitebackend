<?php

namespace App\Http\Controllers\API\v1\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Repositories\Dashboard\IDashboardRepository;

class DashboardController extends ApiController {

    private $dashboardRepository;

    public function __construct(IDashboardRepository $iDashboardRepository) {
        $this->middleware('auth:api');
        $this->dashboardRepository = $iDashboardRepository;
    }

    public function stats(Request $request) {
        $stats = $this->dashboardRepository->stats();
        return $this->responseWithData(200, $stats);
    }

    public function overduesoon(Request $request) {
        $data = $this->dashboardRepository->list_overduesoon($request->all());
        return $this->responseWithData(200, $data);
    }
}
