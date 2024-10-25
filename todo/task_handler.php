<?php
require_once '../config/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $conn = connectDB();
    
    switch ($action) {
        case 'create':
            $listId = filter_var($_POST['list_id'], FILTER_VALIDATE_INT);
            $title = cleanInput($_POST['title'] ?? '');
            
            if (!$listId || empty($title)) {
                http_response_code(400);
                echo json_encode(['error' => 'List ID and title are required']);
                exit();
            }

            // Verify list belongs to user
            $stmt = $conn->prepare("
                SELECT id FROM todo_lists 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$listId, $_SESSION['user_id']]);
            if (!$stmt->fetch()) {
                http_response_code(403);
                echo json_encode(['error' => 'Unauthorized']);
                exit();
            }

            try {
                $stmt = $conn->prepare("
                    INSERT INTO tasks (list_id, title, created_at) 
                    VALUES (?, ?, NOW())
                ");
                
                $stmt->execute([$listId, $title]);
                $newTaskId = $conn->lastInsertId();
                
                // Fetch the newly created task
                $stmt = $conn->prepare("
                    SELECT id, title, status, created_at 
                    FROM tasks 
                    WHERE id = ?
                ");
                $stmt->execute([$newTaskId]);
                $newTask = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Task created successfully',
                    'task' => $newTask
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create task']);
            }
            break;
            
        case 'update_status':
            $taskId = filter_var($_POST['task_id'], FILTER_VALIDATE_INT);
            $status = $_POST['status'] === 'completed' ? 'completed' : 'pending';
            
            if (!$taskId) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid task ID']);
                exit();
            }

            try {
                $stmt = $conn->prepare("
                    UPDATE tasks t
                    JOIN todo_lists l ON t.list_id = l.id
                    SET t.status = ?
                    WHERE t.id = ? AND l.user_id = ?
                ");
                
                $stmt->execute([$status, $taskId, $_SESSION['user_id']]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Task status updated successfully'
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Task not found']);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update task status']);
            }
            break;
            
        case 'delete':
            $taskId = filter_var($_POST['task_id'], FILTER_VALIDATE_INT);
            
            if (!$taskId) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid task ID']);
                exit();
            }

            try {
                $stmt = $conn->prepare("
                    DELETE t FROM tasks t
                    JOIN todo_lists l ON t.list_id = l.id
                    WHERE t.id = ? AND l.user_id = ?
                ");
                
                $stmt->execute([$taskId, $_SESSION['user_id']]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Task deleted successfully'
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Task not found']);
                }
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete task']);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $conn = connectDB();
        $listId = filter_var($_GET['list_id'] ?? null, FILTER_VALIDATE_INT);
        $status = in_array($_GET['status'] ?? '', ['pending', 'completed']) ? $_GET['status'] : null;
        $search = $_GET['search'] ?? null;
        
        $query = "
            SELECT t.* 
            FROM tasks t
            JOIN todo_lists l ON t.list_id = l.id
            WHERE l.user_id = ?
        ";
        $params = [$_SESSION['user_id']];
        
        if ($listId) {
            $query .= " AND t.list_id = ?";
            $params[] = $listId;
        }
        
        if ($status) {
            $query .= " AND t.status = ?";
            $params[] = $status;
        }
        
        if ($search) {
            $query .= " AND t.title LIKE ?";
            $params[] = "%$search%";
        }
        
        $query .= " ORDER BY t.created_at DESC";
        
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($tasks);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch tasks']);
    }
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);