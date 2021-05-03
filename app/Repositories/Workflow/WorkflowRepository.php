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

        $processes = $data['processes'];
        foreach ($processes as $key => $process) {
            $processes[$key]['code'] = preg_replace('/[^A-Za-z0-9\-]/', '_', strtolower($process['name']));
        }

        $workflow->processes()->createMany($processes);
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

        $processes = $data['processes'];
        foreach ($processes as $key => $process) {
            $processes[$key]['code'] = preg_replace('/[^A-Za-z0-9\-]/', '_', strtolower($process['name']));
        }

        // retrieve all the process ids from request
        $reqProcessIds = collect($processes)->map(function($item, $key) {
            return $item['id'];
        })->toArray();

        // retrieve newly added processes
        // $newProcesses = collect($data['processes'])->filter(function($item, $key) {
        //     // if no id means new
        //     if (!isset($item['id']))
        //         return $item;
        // })->toArray();

        // retrieve process that are to be deleted
        $toBeDeleted = Process::where('workflow_id', $workflow->id)
            ->whereNotIn('id', $reqProcessIds)
            ->get();

        

        DB::beginTransaction();
        // delete all workflow process
        $workflow->processes()->delete();
        $workflow->processes()->createMany($processes);

        // update workflow
        $workflow->fill($data);
        $workflow->save();
        // // add / update processes
        // $this->bulk_update($workflow, $data['processes']);
        // // delete processes
        // $workflow->processes()
        //     ->whereIn('id', $toBeDeleted->pluck('id')->toArray())
        //     ->delete();
        // update denorm workflow table
        $this->update_workflow_table($workflow, $data['processes'], $toBeDeleted->toArray());
        DB::commit();
        return $workflow;
    }

    /**
     * {@inheritdoc}
     */
    public function updateColumnWidth(Workflow $workflow, $data) {
        Process::where('workflow_id', $workflow->id)
            ->where('code', $data['code'])
            ->update(['width' => $data['width']]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Workflow $workflow) {
        $tableName = '_workflow_'.$workflow->id;
        DB::beginTransaction();
        // $workflow->processes()->delete();
        $workflow->delete();
        // Schema::dropIfExists($tableName);
        DB::commit();
    }

    private function create_workflow_table(Workflow $workflow) {
        $tableName = '_workflow_'.$workflow->id;
        Schema::create($tableName, function($table) use ($workflow) {
            $table->bigIncrements('id');
            $table->string('iwo', 20)->unique();
            $table->string('company', 200);
            $table->text('description');
            $table->integer('quantity');
            foreach ($workflow->processes as $process) {
                $column_name = $process['code'];
                $table->string($column_name, 100);
            }
            $table->string('person_in_charge', 100)->nullable();
            $table->text('remark')->nullable();
            $table->string('status', 20);
            $table->date('delivery_date')->nullable();
            $table->bigInteger('created_by')->unsigned();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->timestamps();

            $table->index('company');
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
                if (!Schema::hasColumn($tableName, $process['code'])) {
                    $table->string(strtolower($process['code']), 100)->before('remark');
                }
            }
            // delete existing column
            foreach ($deleteProcesses as $process) {
                if (Schema::hasColumn($tableName, $process['code'])) {
                    $table->dropColumn($process['code']);
                }
            }
        });
    }
}