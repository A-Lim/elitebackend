<?php
namespace App\Repositories\Dashboard;

use DB;
use App\Workflow;
use App\Order;
use App\OrderLog;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class DashboardRepository implements IDashboardRepository {

     /**
     * {@inheritdoc}
     */
    public function stats() {
        $stats = [
            'pending_order_count' => 0,
            'overdue_order_count' => 0,
            'completed_today_count' => 0,
            'overdue_this_week_count' => 0
        ];

        // get all workflows
        $workflows = Workflow::where('status', Workflow::STATUS_ACTIVE)
            ->where('deleted_at', null)
            ->get();

        $today = CarbonImmutable::today();

        // total pending orders
        $total_pending_queries = [];
        foreach ($workflows as $workflow) {
            $query = "(SELECT count(*) FROM _workflow_".$workflow->id." WHERE status = '".Order::STATUS_INPROGRESS."')";
            array_push($total_pending_queries, $query);
        }

        if (count($total_pending_queries) > 0) {
            $total_pending_query = "SELECT ".implode('+', $total_pending_queries)." as total FROM dual";
            $stats['pending_order_count'] = DB::select($total_pending_query)[0]->total;
        }

        // overdue 
        $total_overdue_queries = [];
        foreach ($workflows as $workflow) {
            $query = "(SELECT count(*) FROM _workflow_".$workflow->id." WHERE status = '".Order::STATUS_INPROGRESS."' AND delivery_date < '".$today->format('Y-m-d')."')";
            array_push($total_overdue_queries, $query);
        }

        if (count($total_overdue_queries) > 0) {
            $total_overdue_query = "SELECT ".implode('+', $total_overdue_queries)." as total FROM dual";
            $stats['overdue_order_count'] = DB::select($total_overdue_query)[0]->total;
        }

        // completed today
        $stats['completed_today_count'] = OrderLog::join('workflows', 'workflows.id', '=', 'order_logs.workflow_id')
            ->whereDate('order_logs.created_at', $today)
            ->where('order_logs.to_status', 'Completed')
            ->where('workflows.status', Workflow::STATUS_ACTIVE)
            ->count();

        // overdue this week
        $weekStartDate = $today->startOfWeek();
        $weekEndDate = $today->endOfWeek();

        $overdue_this_week_queries = [];
        foreach ($workflows as $workflow) {
            $query = "(SELECT count(*) FROM _workflow_".$workflow->id." WHERE status = '".Order::STATUS_INPROGRESS."' AND delivery_date BETWEEN '".$weekStartDate->format('Y-m-d')."' AND '".$weekEndDate->format('Y-m-d')."')";
            array_push($overdue_this_week_queries, $query);
        }

        if (count($overdue_this_week_queries) > 0) {
            $overdue_this_week_query = "SELECT ".implode('+', $overdue_this_week_queries)." as total FROM dual";
            $stats['overdue_this_week_count'] = DB::select($overdue_this_week_query)[0]->total;
        }


        return $stats;
    }

    public function list_overduesoon($data) {
        $filter = 'week';
        if (isset($data['range']))
            $filter = $data['range'];


        // get all workflows
        $workflows = Workflow::where('status', Workflow::STATUS_ACTIVE)
            ->where('deleted_at', null)
            ->get();

        $today = CarbonImmutable::today();
        $weekStartDate = $today->startOfWeek();
        $weekEndDate = $today->endOfWeek();

        $overdue_this_week_queries = [];
        foreach ($workflows as $workflow) {
            $query = "(
                SELECT id as order_id, ".$workflow->id." as workflow_id, 
                iwo, company, 
                DATE_FORMAT(delivery_date, '%d-%m-%Y') as delivery_date,
                CASE WHEN delivery_date >= CURDATE() THEN 0 ELSE 1 END AS expired FROM _workflow_".$workflow->id." WHERE status = '".Order::STATUS_INPROGRESS."' AND ".$this->buildWhereQuery($filter).")";
            array_push($overdue_this_week_queries, $query);
        }

        if (count($overdue_this_week_queries) > 0) {
            $overdue_this_week_query = implode(' UNION ', $overdue_this_week_queries);
            return DB::select($overdue_this_week_query);
        }

        return [];
    }

    private function buildWhereQuery($type) {
        $today = CarbonImmutable::today();

        switch ($type) {
            case 'today':
                return "delivery_date = '".$today->format('Y-m-d')."'";
                break;

            case 'week':
                $weekStartDate = $today->startOfWeek();
                $weekEndDate = $today->endOfWeek();
                return "delivery_date BETWEEN '".$weekStartDate->format('Y-m-d')."' AND '".$weekEndDate->format('Y-m-d')."'";
                break;

            case 'month':
                $monthStartDate = $today->startOfMonth();
                $monthEndDate = $today->endOfMonth();
                return "delivery_date BETWEEN '".$monthStartDate->format('Y-m-d')."' AND '".$monthEndDate->format('Y-m-d')."'";
                break;
        }
    }
}