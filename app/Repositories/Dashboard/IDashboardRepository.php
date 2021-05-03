<?php
namespace App\Repositories\Dashboard;

interface IDashboardRepository {
    /**
     * Stats for dashboard
     * @return array
     */
    public function stats();

    /**
     * List overdue soon orders
     * @param array
     * @return array
     */
    public function list_overduesoon($data);
}