<?php
/**
 * ReqHub User Manager
 * 
 * File: reqhub/includes/UserManager.php
 * 
 * Purpose: Handles all ReqHub-specific user management and role control.
 * Works in tandem with ZenHubIntegration for authentication.
 * 
 * Database connection via SampleDatabase class from parent ZenHub
 */

class UserManager {
    private $reqHubDb;
    private $zenHubIntegration;
    private $userTable = 'users';
    
    /**
     * Role hierarchy - higher number = more permissions
     * Modify as needed for your organization
     */
    private $roleHierarchy = [
        'Requestor' => 1,    // Can create requests
        'Reviewer' => 2,     // Can review and comment on requests
        'Approver' => 3,     // Can approve/reject requests
        'Admin' => 4         // Full system access
    ];

    /**
     * Constructor
     * 
     * @param PDO $reqHubDb Database connection to ReqHub database
     * @param ZenHubIntegration $zenHubIntegration Integration layer
     */
    public function __construct($reqHubDb, $zenHubIntegration) {
        $this->reqHubDb = $reqHubDb;
        $this->zenHubIntegration = $zenHubIntegration;
    }

    /**
     * Get current logged-in user with complete profile
     * 
     * This is the PRIMARY method for getting user information in ReqHub.
     * It combines ZenHub identity + ReqHub role.
     * 
     * Returns null if user is not authenticated.
     * 
     * @return array|null Complete user profile
     */
    public function getCurrentUser() {
        // Step 1: Verify authentication at ZenHub level
        $zenHubUser = $this->zenHubIntegration->getCurrentZenHubUser();
        if (!$zenHubUser) {
            return null;
        }

        // Step 2: Get ReqHub role for this user
        $empNo = $zenHubUser['emp_no'];
        $roleData = $this->getReqHubRole($empNo);

        // Step 3: Return combined profile
        return [
            'user_id' => $zenHubUser['user_id'],
            'emp_no' => $empNo,
            'name' => $zenHubUser['name'],
            'email' => $zenHubUser['email'],
            'reqhub_role' => $roleData['role'],
            'is_active' => $zenHubUser['is_active'] && $roleData['is_active']
        ];
    }

    /**
     * Get ReqHub role for a specific employee
     * 
     * If user doesn't exist in ReqHub yet, returns default role.
     * This supports auto-provisioning of new users.
     * 
     * Note: This table name is 'users' in your ReqHub, not 'reqhub_users'
     * 
     * @param string $empNo Employee number from ZenHub
     * @return array Role information
     */
    public function getReqHubRole($empNo) {
        // Using 'users' table as per your actual ReqHub structure
        // After migration, should only have: id, employee_id, reqhub_role, is_active, etc.
        $sql = "SELECT id, employee_id, reqhub_role, is_active FROM {$this->userTable} WHERE employee_id = ?";
        
        $stmt = $this->reqHubDb->prepare($sql);
        $stmt->execute([$empNo]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // If user not found in ReqHub
        if (!$result) {
            // Return default role (user can be auto-provisioned later)
            return [
                'role' => 'Requestor',      // Default role
                'is_active' => true,
                'exists_in_reqhub' => false
            ];
        }

        // User exists in ReqHub
        return [
            'role' => $result['reqhub_role'],
            'is_active' => (bool)$result['is_active'],
            'exists_in_reqhub' => true
        ];
    }

    /**
     * Check if a user has a minimum required role
     * 
     * Uses role hierarchy. For example:
     * - hasRole('emp123', 'Approver') returns true if user is Approver or Admin
     * - hasRole('emp123', 'Admin') only returns true if user is Admin
     * 
     * @param string $empNo Employee number
     * @param string $requiredRole Minimum role required
     * @return bool True if user has required role (or higher)
     */
    public function hasRole($empNo, $requiredRole) {
        // Validate role exists
        if (!isset($this->roleHierarchy[$requiredRole])) {
            throw new Exception('Invalid role: ' . $requiredRole);
        }

        $role = $this->getReqHubRole($empNo);
        
        // User must be active
        if (!$role['is_active']) {
            return false;
        }

        // Check hierarchy
        $userLevel = $this->roleHierarchy[$role['role']] ?? 0;
        $requiredLevel = $this->roleHierarchy[$requiredRole];

        return $userLevel >= $requiredLevel;
    }

    /**
     * Check if user has exact role (no hierarchy check)
     * 
     * @param string $empNo Employee number
     * @param string $role Exact role to check
     * @return bool
     */
    public function hasExactRole($empNo, $role) {
        $userRole = $this->getReqHubRole($empNo);
        return $userRole['role'] === $role && $userRole['is_active'];
    }

    /**
     * Assign or update a user's ReqHub role
     * 
     * This should only be called by admins.
     * Check authorization before calling this!
     * 
     * @param string $empNo Employee number to assign role to
     * @param string $newRole New role (Requestor, Reviewer, Approver, Admin)
     * @param bool $isActive Whether user should be active
     * @return bool Success
     * @throws Exception If role is invalid or user is not authorized
     */
    public function assignRole($empNo, $newRole, $isActive = true) {
        // Validate new role
        $validRoles = array_keys($this->roleHierarchy);
        if (!in_array($newRole, $validRoles)) {
            throw new Exception('Invalid role: ' . $newRole);
        }

        // Verify calling user is admin (you should check this in your controller!)
        $currentUser = $this->getCurrentUser();
        if (!$currentUser || !$this->hasRole($currentUser['emp_no'], 'Admin')) {
            throw new Exception('Unauthorized: Admin role required');
        }

        // Check if user already exists in ReqHub
        $existing = $this->getReqHubRole($empNo);

        try {
            if (!$existing['exists_in_reqhub']) {
                // Insert new user
                $sql = "INSERT INTO {$this->userTable} 
                        (employee_id, reqhub_role, is_active) 
                        VALUES (?, ?, ?)";
                $stmt = $this->reqHubDb->prepare($sql);
                $stmt->execute([$empNo, $newRole, (int)$isActive]);
            } else {
                // Update existing user
                $sql = "UPDATE {$this->userTable} 
                        SET reqhub_role = ?, is_active = ?, updated_at = NOW() 
                        WHERE employee_id = ?";
                $stmt = $this->reqHubDb->prepare($sql);
                $stmt->execute([$newRole, (int)$isActive, $empNo]);
            }
            
            return true;
        } catch (PDOException $e) {
            throw new Exception('Database error: ' . $e->getMessage());
        }
    }

    /**
     * Activate or deactivate a user in ReqHub
     * 
     * User can be deactivated without deleting their record.
     * 
     * @param string $empNo Employee number
     * @param bool $isActive True to activate, false to deactivate
     * @return bool Success
     */
    public function setUserActive($empNo, $isActive = true) {
        $sql = "UPDATE {$this->userTable} 
                SET is_active = ?, updated_at = NOW() 
                WHERE employee_id = ?";
        
        try {
            $stmt = $this->reqHubDb->prepare($sql);
            return $stmt->execute([(int)$isActive, $empNo]);
        } catch (PDOException $e) {
            throw new Exception('Database error: ' . $e->getMessage());
        }
    }

    /**
     * Auto-provision a new user in ReqHub
     * 
     * Called automatically when a ZenHub user accesses ReqHub for first time.
     * Creates a record with default role (Requestor).
     * 
     * @param string $empNo Employee number from ZenHub
     * @return bool Success
     */
    public function provisionNewUser($empNo) {
        // Check if already exists
        $existing = $this->getReqHubRole($empNo);
        if ($existing['exists_in_reqhub']) {
            return true; // Already provisioned
        }

        // Verify user exists in ZenHub
        if (!$this->zenHubIntegration->userExists($empNo)) {
            throw new Exception('User not found in ZenHub: ' . $empNo);
        }

        // Create with default role
        $sql = "INSERT INTO {$this->userTable} 
                (employee_id, reqhub_role, is_active) 
                VALUES (?, 'Requestor', 1)";
        
        try {
            $stmt = $this->reqHubDb->prepare($sql);
            return $stmt->execute([$empNo]);
        } catch (PDOException $e) {
            // Might fail if user already exists (race condition)
            // That's OK - just return true
            return true;
        }
    }

    /**
     * Get all users with their roles (for admin dashboard)
     * 
     * @param bool $activeOnly If true, only return active users
     * @param int $limit Optional limit
     * @param int $offset Optional offset for pagination
     * @return array
     */
    public function getAllUsersWithRoles($activeOnly = false, $limit = null, $offset = 0) {
        $sql = "SELECT id, employee_id, reqhub_role, is_active, created_at, updated_at
                FROM {$this->userTable}";
        
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        
        $sql .= " ORDER BY employee_id ASC";
        
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

        return $this->reqHubDb->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get count of users by role
     * 
     * Useful for admin dashboard statistics
     * 
     * @return array Array with role => count
     */
    public function getUserCountByRole() {
        $sql = "SELECT reqhub_role, COUNT(*) as count
                FROM {$this->userTable}
                WHERE is_active = 1
                GROUP BY reqhub_role
                ORDER BY reqhub_role ASC";
        
        $results = $this->reqHubDb->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert to associative array
        $counts = [];
        foreach ($results as $row) {
            $counts[$row['reqhub_role']] = (int)$row['count'];
        }
        
        return $counts;
    }

    /**
     * Get all users with a specific role
     * 
     * @param string $role Role to filter by
     * @param bool $activeOnly If true, only return active users
     * @return array
     */
    public function getUsersByRole($role, $activeOnly = true) {
        $sql = "SELECT id, employee_id, reqhub_role, is_active, created_at
                FROM {$this->userTable}
                WHERE reqhub_role = ?";
        
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        
        $sql .= " ORDER BY employee_id ASC";
        
        $stmt = $this->reqHubDb->prepare($sql);
        $stmt->execute([$role]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get role hierarchy (for debugging/admin)
     * 
     * @return array
     */
    public function getRoleHierarchy() {
        return $this->roleHierarchy;
    }

    /**
     * Get all available roles
     * 
     * @return array
     */
    public function getAvailableRoles() {
        return array_keys($this->roleHierarchy);
    }

    /**
     * Bulk assign roles
     * 
     * Useful for importing or syncing users from ZenHub.
     * Each entry should be: ['employee_id' => 'emp123', 'role' => 'Approver']
     * 
     * @param array $assignments Array of assignment data
     * @return array Results with success/fail counts
     */
    public function bulkAssignRoles($assignments) {
        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($assignments as $assignment) {
            try {
                if (empty($assignment['employee_id']) || empty($assignment['role'])) {
                    $failed++;
                    $errors[] = 'Invalid assignment structure';
                    continue;
                }

                $empNo = $assignment['employee_id'];
                $role = $assignment['role'];

                // Provision if needed
                try {
                    $this->provisionNewUser($empNo);
                } catch (Exception $e) {
                    // User might not exist - that's OK
                }

                // Update role (bypassing authorization check for bulk ops)
                $sql = "INSERT INTO {$this->userTable} 
                        (employee_id, reqhub_role, is_active) 
                        VALUES (?, ?, 1)
                        ON DUPLICATE KEY UPDATE 
                        reqhub_role = ?, updated_at = NOW()";
                
                $stmt = $this->reqHubDb->prepare($sql);
                if ($stmt->execute([$empNo, $role, $role])) {
                    $success++;
                } else {
                    $failed++;
                }
            } catch (Exception $e) {
                $failed++;
                $errors[] = 'Error for ' . ($assignment['employee_id'] ?? 'unknown') . ': ' . $e->getMessage();
            }
        }

        return [
            'success' => $success,
            'failed' => $failed,
            'errors' => $errors
        ];
    }
}

?>
