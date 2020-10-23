<?php
namespace App\Repositories\Workflow;

use DB;
use App\Workflow;
use App\Process;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class WorkflowRepository implements IWorkflowRepository {
    /**
     * {@inheritdoc}
     */
    public function nameExists($name, $workflowId = null) {
        $conditions = [['name', '=', $name]];
        if ($workflowId != null)
            array_push($conditions, ['id', '<>', $workflowId]);

        return Workflow::where($conditions)->exists();
    }

     /**
     * {@inheritdoc}
     */
    public function list($data, $paginate = false) {
        $query = Workflow::buildQuery($data)
            ->withCount('processes');

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
        return Workflow::with('processes')->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function create($data) {
        $data['created_by'] = auth()->id();

        DB::beginTransaction();
        $workflow = Workflow::create($data);
        $workflow->processes()->createMany($data['processes']);
        $this->create_workflow_table($workflow);
        DB::commit();

        return $workflow;
    }

    /**
     * {@inheritdoc}
     */
    public function update_status(Workflow $workflow, $data) {
        return $workflow->update($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(Workflow $workflow, $data) {
        $data['updated_by'] = auth()->id();
        // retrieve all the process ids from request
        $reqProcessIds = collect($data['processes'])->map(function($item, $key) {
            return $item['id'];
        })->toArray();

        // retrieve newly added processes
        $newProcesses = collect($data['processes'])->filter(function($item, $key) {
            // if no id means new
            if (!isset($item['id']))
                return $item;
        })->toArray();

        // retrieve process that are to be deleted
        $toBeDeleted = Process::where('workflow_id', $workflow->id)
            ->whereNotIn('id', $reqProcessIds)
            ->get();

        DB::beginTransaction();
        // update workflow
        $workflow->fill($data);
        $workflow->save();
        // add / update processes
        $this->bulk_update($workflow, $data['processes']);
        // delete processes
        $workflow->processes()
            ->whereIn('id', $toBeDeleted->pluck('id')->toArray())
            ->delete();
        // update denorm workflow table
        $this->update_workflow_table($workflow, $data['processes'], $toBeDeleted->toArray());
        DB::commit();
        return $workflow;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Workflow $workflow) {
        $tableName = '_workflow_'.$workflow->id;
        DB::beginTransaction();
        $workflow->processes()->delete();
        $workflow->delete();
        Schema::dropIfExists($tableName);
        DB::commit();
    }

    private function create_workflow_table(Workflow $workflow) {
        $tableName = '_workflow_'.$workflow->id;
        Schema::create($tableName, function($table) use ($workflow) {
            $table->bigIncrements('id');
            $table->string('iwo', 20)->unique();
            $table->string('customer', 200);
            foreach ($workflow->processes as $process) {
                $column_name = strtolower(str_replace(' ', '_', $process['name']));
                $table->string($column_name, 100);
            }
            $table->text('remark')->nullable();
            $table->string('status', 20);
            $table->date('delivery_date')->nullable();
            $table->bigInteger('created_by')->unsigned();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->timestamps();

            $table->index('customer');
            $table->index('status');
        });
    }

    private function bulk_update(Workflow $workflow, array $processes) {
        foreach ($processes as $index => $process) {
            $processes[$index]['workflow_id'] = $workflow->id;
        }

        $table = 'processes';
        $first = reset($processes);

        $columns = implode(',', array_map(function($value) {
            return "`$value`";
        }, array_keys($first)));

        $values = implode(',', array_map(function($row) {
            $process_val = implode( ',', array_map(function($value) { 
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                return '"'.str_replace('"', '""', $value).'"'; 
            } , $row));
            return '('.$process_val.')';
        }, $processes));

        $updates = implode(',', array_map(function($value) {
            return "`$value` = VALUES(`$value`)";
        } , array_keys($first)));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES {$values} ON DUPLICATE KEY UPDATE {$updates}";
        DB::statement($sql);
    }

    private function update_workflow_table(Workflow $workflow, array $addProcesses, array $deleteProcesses) {
        $tableName = '_workflow_'.$workflow->id;
        Schema::table($tableName, function($table) use ($tableName, $workflow, $addProcesses, $deleteProcesses) {
            // add new column
            foreach ($addProcesses as $process) {
                $column_name = strtolower(str_replace(' ', '_', $process['name']));
                if (!Schema::hasColumn($tableName, $column_name)) {
                    $table->string(strtolower($column_name), 100)->before('remark');
                }
            }
            // delete existing column
            foreach ($deleteProcesses as $process) {
                $column_name = strtolower($process['name']);
                if (Schema::hasColumn($tableName, $column_name)) {
                    $table->dropColumn($column_name);
                }
            }
        });
    }
}