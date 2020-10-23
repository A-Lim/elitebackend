<?php
namespace App\Repositories\Workflow;

use App\Workflow;

interface IWorkflowRepository
{
    /**
     * Check if workflow name is exist
     */
    public function nameExists($name, $workflowId = null);

     /**
     * List workflows
     * 
     * @param array $query
     * @param boolean $paginate = false
     * @return [UserGroup]
     */
    public function list($query, $paginate = false);

    /**
     * Find workflow from id
     * 
     * @param integer $id
     * @return Workflow
     */
    public function find($id);

    /**
     * Creates a workflow
     * 
     * @param array $data
     * @return Workflow
     */
    public function create($data);

    /**
     * Update workflow status
     * 
     * @param array $data
     * @return Workflow
     */
    public function update_status(Workflow $workflow, $data);

    /**
     * Updates a workflow (including processes)
     * 
     * @param Workflow $workflow
     * @param array $data
     * @return Workflow
     */
    public function update(Workflow $workflow, $data);

    /**
     * Deletes a workflow
     * 
     * @param Workflow $workflow
     * @return void
     */
    public function delete(Workflow $workflow);

}