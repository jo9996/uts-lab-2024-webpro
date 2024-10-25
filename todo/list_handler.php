<?php
require_once '../config/config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $conn = connectDB();
    
    switch ($action) {
        case 'create':
            $title = cleanInput($_POST['title'] ?? '');
            
            if (empty($title)) {
                http_response_code(400);
                echo json_encode(['error' => 'Title is required']);
                exit();
            }

            try {
                $stmt = $conn->prepare("
                    INSERT INTO todo_lists (user_id, title, created_at) 
                    VALUES (?, ?, NOW())
                ");
                
                $stmt->execute([$_SESSION['user_id'], $title]);
                $newListId = $conn->lastInsertId();
                
                // Fetch the newly created list
                $stmt = $conn->prepare("
                    SELECT id, title, created_at 
                    FROM todo_lists 
                    WHERE id = ?
                ");
                $stmt->execute([$newListId]);
                $newList = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'List created successfully',
                    'list' => $newList
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create list']);
            }
            break;
            
        case 'delete':
            $listId = filter_var($_POST['list_id'], FILTER_VALIDATE_INT);
            
            if (!$listId) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid list ID']);
                exit();
            }

            try {
                $conn->beginTransaction();
                
                // Delete the list and its tasks (cascading will handle tasks)
                $stmt = $conn->prepare("
                    DELETE FROM todo_lists 
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$listId, $_SESSION['user_id']]);
                
                $conn->commit();
                echo json_encode([
                    'success' => true,
                    'message' => 'List deleted successfully'
                ]);
            } catch (PDOException $e) {
                $conn->rollBack();
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete list']);
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
        $stmt = $conn->prepare("
            SELECT id, title, created_at 
            FROM todo_lists 
            WHERE user_id = ? 
            ORDER BY created_at DESC
        ");
        
        $stmt->execute([$_SESSION['user_id']]);
        $lists = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($lists);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch lists']);
    }
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);