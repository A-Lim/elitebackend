<?php
namespace App\Repositories\Order;

use App\Workflow;

interface IOrderRepository {

    /**
     * Check if iwo exists
     * 
     * @param Workflow $workflow
     * @param string $iwo
     * @param integer $order_id
     * @return boolean
     */
    public function iwoExists(Workflow $workflow, $iwo, $orderId = null);

    /**
     * List orders
     * 
     * @param Workflow $workflow
     * @param array $query
     * @param boolean $withFiles = false
     * @param boolean $paginate = false
     * @return [Orders]
     */
    public function list(Workflow $workflow, $query, $withFiles = false, $paginate = false);

    /**
     * Retrieve order details
     * 
     * @param Workflow $workflow
     * @param integer $order_id
     */
    public function find(Workflow $workflow, $order_id);

    /**
     * Create order
     * 
     * @param Workflow $workflow
     * @param array $data
     * @param File $files
     * @return null
     */
    public function create(Workflow $workflow, $data, $files = null);

    /**
     * Delete order
     * 
     * @param Workflow $workflow
     * @param integer $order_id
     * @return null
     */
    public function delete(Workflow $workflow, $order_id);

    /**
     * Update order process status
     * 
     * @param Workflow $workflow
     * @param integer $order_id
     * @param array $data
     */
    public function updateProcess(Workflow $workflow, $order_id, $data);
}