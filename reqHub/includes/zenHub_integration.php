<?php
/**
 * ZenHub Integration Layer - WITH LOGGING
 * 
 * File: reqhub/includes/zenHub_integration.php
 */

class ZenHubIntegration {
    
    private $zenHubDb;
    
    private $zenHubSessionKey = 'user_id';
    
    private $zenHubUserTable = 'tbl_user2';
    private $zenHubUserIdColumn = 'U_ID';
    private $zenHubEmpNoColumn = 'Emp_No';
    private $zenHubNameColumn = 'U_Name';
    private $zenHubEmailColumn = 'U_Email';
    private $zenHubStatusColumn = 'U_stat';
    
    /**
     * Alternative table for employee names (tbl201_basicinfo)
     */
    private $basicInfoTable = 'tbl201_basicinfo';
    private $basicInfoEmpIdColumn = 'bi_empno';
    private $basicInfoNameColumn = 'bi_empfname';

    public function __construct($zenHubDbConnection) {
        $this->zenHubDb = $zenHubDbConnection;
        error_log("ZenHubIntegration initialized");
    }

    public function getCurrentZenHubUser() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        error_log("getCurrentZenHubUser called");
        error_log("Checking session['user_id']: " . ($_SESSION['user_id'] ?? 'NOT SET'));

        if (empty($_SESSION['user_id'])) {
            error_log("❌ ZenHub session missing user_id - returning NULL");
            return null;
        }

        $empNo = $_SESSION['user_id'];
        $userIdFromSession = $_SESSION['HR_UID'] ?? null;

        error_log("✓ ZenHub session found: user_id=$empNo, HR_UID=$userIdFromSession");
        error_log("Fetching user data from database for empNo: $empNo");
        
        $userData = $this->getZenHubUserByEmpNo($empNo);

        if (!$userData) {
            error_log("❌ User $empNo not found in ZenHub database - returning NULL");
            return null;
        }

        error_log("✓ User data retrieved: " . json_encode($userData));
        return $userData;
    }

    public function isUserAuthenticated() {
        $user = $this->getCurrentZenHubUser();
        $result = $user !== null && $user['is_active'] === true;
        error_log("isUserAuthenticated: " . ($result ? "TRUE" : "FALSE"));
        return $result;
    }

    public function getZenHubUserByEmpNo($empNo) {
        error_log("=== getZenHubUserByEmpNo START ===");
        error_log("empNo: $empNo");
        
        $sql = "SELECT {$this->zenHubUserIdColumn}, {$this->zenHubEmpNoColumn}, 
                       {$this->zenHubNameColumn}, {$this->zenHubEmailColumn}, 
                       {$this->zenHubStatusColumn}
                FROM {$this->zenHubUserTable} 
                WHERE {$this->zenHubEmpNoColumn} = ? AND {$this->zenHubStatusColumn} = 1";
        
        error_log("SQL Query: " . $sql);
        
        try {
            $stmt = $this->zenHubDb->prepare($sql);
            error_log("Query prepared");
            
            $stmt->execute([$empNo]);
            error_log("Query executed");
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Query result from tbl_user2: " . json_encode($result));

            if (!$result) {
                error_log("❌ User $empNo not found in tbl_user2");
                return null;
            }

            $name = $result[$this->zenHubNameColumn];
            error_log("Initial name from tbl_user2: $name");
            
            // TRY TO GET NAME FROM tbl_201_basicinfo
            error_log("ATTEMPTING TO FETCH FROM tbl_201_basicinfo...");
            error_log("Table: {$this->basicInfoTable}, Column for empno: {$this->basicInfoEmpIdColumn}, Column for name: {$this->basicInfoNameColumn}");
            
            try {
                $basicInfoSql = "SELECT {$this->basicInfoNameColumn} 
                                 FROM {$this->basicInfoTable} 
                                 WHERE {$this->basicInfoEmpIdColumn} = ?
                                 LIMIT 1";
                error_log("Basic info SQL: " . $basicInfoSql);
                error_log("Executing with empNo: $empNo");
                
                $basicStmt = $this->zenHubDb->prepare($basicInfoSql);
                $basicStmt->execute([$empNo]);
                $basicInfo = $basicStmt->fetch(PDO::FETCH_ASSOC);
                
                error_log("Basic info result: " . json_encode($basicInfo));
                
                if ($basicInfo && !empty($basicInfo[$this->basicInfoNameColumn])) {
                    error_log("✓✓✓ FOUND NAME IN tbl_201_basicinfo: " . $basicInfo[$this->basicInfoNameColumn]);
                    $name = $basicInfo[$this->basicInfoNameColumn];
                } else {
                    error_log("❌ No name found in tbl_201_basicinfo, USING FALLBACK: $name");
                }
            } catch (PDOException $e) {
                error_log("❌ ERROR querying tbl_201_basicinfo: " . $e->getMessage());
                error_log("USING FALLBACK NAME: $name");
            }

            error_log("FINAL NAME TO USE: $name");

            $userData = [
                'user_id' => $result[$this->zenHubUserIdColumn],
                'emp_no' => $result[$this->zenHubEmpNoColumn],
                'name' => $name,
                'email' => $result[$this->zenHubEmailColumn],
                'is_active' => true
            ];

            error_log("✓ Final userData: " . json_encode($userData));
            error_log("=== getZenHubUserByEmpNo END ===");
            return $userData;
        } catch (PDOException $e) {
            error_log("❌ getZenHubUserByEmpNo error: " . $e->getMessage());
            return null;
        }
    }

    public function getZenHubUserById($userId) {
        error_log("getZenHubUserById called with userId: $userId");
        
        $sql = "SELECT {$this->zenHubUserIdColumn}, {$this->zenHubEmpNoColumn}, 
                       {$this->zenHubNameColumn}, {$this->zenHubEmailColumn}, 
                       {$this->zenHubStatusColumn}
                FROM {$this->zenHubUserTable} 
                WHERE {$this->zenHubUserIdColumn} = ? AND {$this->zenHubStatusColumn} = 1";
        
        try {
            $stmt = $this->zenHubDb->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("Query result for userId $userId: " . json_encode($result));

            if (!$result) {
                error_log("❌ getZenHubUserById: User ID $userId not found");
                return null;
            }

            return [
                'user_id' => $result[$this->zenHubUserIdColumn],
                'emp_no' => $result[$this->zenHubEmpNoColumn],
                'name' => $result[$this->zenHubNameColumn],
                'email' => $result[$this->zenHubEmailColumn],
                'is_active' => true
            ];
        } catch (PDOException $e) {
            error_log("❌ getZenHubUserById error: " . $e->getMessage());
            return null;
        }
    }

    public function getAllActiveZenHubUsers($limit = null) {
        $sql = "SELECT {$this->zenHubUserIdColumn}, {$this->zenHubEmpNoColumn}, 
                       {$this->zenHubNameColumn}, {$this->zenHubEmailColumn}
                FROM {$this->zenHubUserTable} 
                WHERE {$this->zenHubStatusColumn} = 1 
                ORDER BY {$this->zenHubNameColumn} ASC";
        
        if ($limit !== null) {
            $sql .= " LIMIT " . (int)$limit;
        }

        try {
            $results = $this->zenHubDb->query($sql)->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function($user) {
                return [
                    'user_id' => $user[$this->zenHubUserIdColumn],
                    'emp_no' => $user[$this->zenHubEmpNoColumn],
                    'name' => $user[$this->zenHubNameColumn],
                    'email' => $user[$this->zenHubEmailColumn]
                ];
            }, $results);
        } catch (PDOException $e) {
            error_log("❌ getAllActiveZenHubUsers error: " . $e->getMessage());
            return [];
        }
    }

    public function searchZenHubUsers($searchTerm) {
        $searchTerm = '%' . $searchTerm . '%';
        
        $sql = "SELECT {$this->zenHubUserIdColumn}, {$this->zenHubEmpNoColumn}, 
                       {$this->zenHubNameColumn}, {$this->zenHubEmailColumn}
                FROM {$this->zenHubUserTable} 
                WHERE {$this->zenHubStatusColumn} = 1 
                AND ({$this->zenHubNameColumn} LIKE ? OR {$this->zenHubEmpNoColumn} LIKE ?)
                ORDER BY {$this->zenHubNameColumn} ASC
                LIMIT 50";
        
        try {
            $stmt = $this->zenHubDb->prepare($sql);
            $stmt->execute([$searchTerm, $searchTerm]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("❌ searchZenHubUsers error: " . $e->getMessage());
            return [];
        }
    }

    public function userExists($empNo) {
        return $this->getZenHubUserByEmpNo($empNo) !== null;
    }

    public function getSessionKey() {
        return $this->zenHubSessionKey;
    }

    public function setSessionKey($key) {
        $this->zenHubSessionKey = $key;
    }

    public function debugGetRawSessionData() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return [
            'user_id' => $_SESSION['user_id'] ?? null,
            'HR_UID' => $_SESSION['HR_UID'] ?? null,
            'Emp_No' => $_SESSION['Emp_No'] ?? null,
            'name' => $_SESSION['name'] ?? null,
            'email' => $_SESSION['email'] ?? null
        ];
    }
}

?>