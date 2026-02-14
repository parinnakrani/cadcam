<?php

namespace App\Controllers\Masters;

use App\Controllers\BaseController;
use App\Services\Master\ProcessService;
use App\Models\ProcessModel;
use App\Services\Audit\AuditService;
use CodeIgniter\Exceptions\PageNotFoundException;
use Exception;

/**
 * ProcessController
 *
 * Controller for managing manufacturing processes (CRUD, Rates, etc.)
 */
class ProcessController extends BaseController
{
    protected $processService;
    
    // Enum values for view dropdowns matching migration
    protected $processTypes = ['Rhodium', 'Meena', 'Wax', 'Polish', 'Coating', 'Other'];

    public function __construct()
    {
        // Manual DI wrapper
        $this->processService = new ProcessService(
            new ProcessModel(),
            new AuditService()
        );
    }

    /**
     * Display list of processes.
     *
     * @return string|\CodeIgniter\HTTP\ResponseInterface
     */
    public function index()
    {
        if (!$this->hasPermission('process.view')) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
            }
            throw new PageNotFoundException("Access Denied");
        }

        $type = $this->request->getGet('process_type');

        // Service method getActiveProcesses accepts type filter
        // Handles "is_active" implicitly (returns active ones). 
        // If we want ALL (including inactive) for master list, service might need adjustment or new method.
        // Prompt says "Load processes via ProcessService". 
        // Service has getActiveProcesses. Assuming index shows active processes or filtered list.
        $processes = $this->processService->getActiveProcesses($type);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['data' => $processes]);
        }

        $data = [
            'title'        => 'Processes',
            'processes'    => $processes,
            'processTypes' => $this->processTypes,
            'canCreate'    => $this->hasPermission('process.create'),
            'canEdit'      => $this->hasPermission('process.edit'),
            'canDelete'    => $this->hasPermission('process.delete')
        ];

        return view('Masters/Processes/index', $data);
    }

    /**
     * Show form to create a new process.
     *
     * @return string
     */
    public function create(): string
    {
        if (!$this->hasPermission('process.create')) {
            throw new PageNotFoundException("Access Denied");
        }

        $data = [
            'title'        => 'Create Process',
            'processTypes' => $this->processTypes
        ];

        return view('Masters/Processes/create', $data);
    }

    /**
     * Store a new process.
     *
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function store()
    {
        if (!$this->hasPermission('process.create')) {
            return redirect()->back()->with('error', 'Access Denied');
        }

        try {
            $data = $this->request->getPost();
            
            $this->processService->createProcess($data);

            return redirect()->to('masters/processes')->with('message', 'Process created successfully.');
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Display process details.
     *
     * @param int $id
     * @return string
     */
    public function show(int $id): string
    {
        if (!$this->hasPermission('process.view')) {
             throw new PageNotFoundException("Access Denied");
        }

        $process = $this->processService->getProcessById($id);
        
        if (!$process) {
            throw new PageNotFoundException("Process not found: $id");
        }

        $data = [
            'title'   => 'Process Details',
            'process' => $process
        ];

        return view('Masters/Processes/show', $data);
    }

    /**
     * Show form to edit a process.
     *
     * @param int $id
     * @return string
     */
    public function edit(int $id): string
    {
        if (!$this->hasPermission('process.edit')) {
            throw new PageNotFoundException("Access Denied");
        }

        $process = $this->processService->getProcessById($id);
        
        if (!$process) {
            throw new PageNotFoundException("Process not found: $id");
        }

        $data = [
            'title'        => 'Edit Process',
            'process'      => $process,
            'processTypes' => $this->processTypes // Pass types for dropdown
        ];

        return view('Masters/Processes/edit', $data);
    }

    /**
     * Update an existing process.
     *
     * @param int $id
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    public function update(int $id)
    {
        if (!$this->hasPermission('process.edit')) {
            return redirect()->back()->with('error', 'Access Denied');
        }

        try {
            $data = $this->request->getPost();
            
            // Check for price change message before update
            $message = 'Process updated successfully.';
            $oldProcess = $this->processService->getProcessById($id);
            
            if ($oldProcess && isset($data['rate_per_unit'])) {
                $oldRate = (float)$oldProcess['rate_per_unit'];
                $newRate = (float)$data['rate_per_unit'];
                if (abs($oldRate - $newRate) > 0.0001) {
                    $message = 'Price updated - change logged.';
                }
            }

            $this->processService->updateProcess($id, $data);

            return redirect()->to('masters/processes')->with('message', $message);
        } catch (Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete a process (Soft Delete).
     *
     * @param int $id
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function delete(int $id)
    {
        if (!$this->hasPermission('process.delete')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
        }

        try {
            $this->processService->deleteProcess($id);
            session()->setFlashdata('message', 'Process deleted successfully.');
            
            return $this->response->setJSON(['status' => 'success', 'message' => 'Process deleted successfully.']);
        } catch (Exception $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => $e->getMessage()])->setStatusCode(400);
        }
    }

    /**
     * Get active processes by type (API).
     *
     * @param string $type
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function getByType(string $type)
    {
        if (!$this->hasPermission('process.view')) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Access Denied'])->setStatusCode(403);
        }

        $results = $this->processService->getProcessesByType($type);
        
        return $this->response->setJSON($results);
    }
}
