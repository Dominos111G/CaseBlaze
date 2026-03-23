<?php
include "config.php";
include "connect.php";

if ($_SERVER['REQUEST_METHOD'] != "POST") {
    header("Location: /index.php");
    exit;
}
if (!isset($_POST['i_id']) || !isset($_POST['back'])) {
    header("Location: /index.php");
    exit;
}
if (!isset($_SESSION['user_id']) || !isset($_SESSION['wallet'])) {
    header("Location: /login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$back = htmlspecialchars($_POST['back']);
$item_id = $_POST['i_id']; // Nie rzutujemy na int od razu, bo może być "all"

// Sprawdź czy to sprzedaż wszystkich przedmiotów
if ($item_id === "all") {
    // Sprzedaj wszystkie przedmioty użytkownika
    $conn->begin_transaction();
    
    try {
        // Pobierz wszystkie przedmioty użytkownika z ich cenami sprzedaży
        $query = "SELECT inv.id, i.sell_price 
                  FROM inventory AS inv 
                  INNER JOIN items AS i ON inv.item_id = i.id 
                  WHERE inv.user_id = ? AND inv.locked = 0";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $total_value = 0;
        $items_to_delete = [];
        
        while ($row = $result->fetch_assoc()) {
            $total_value += $row['sell_price'];
            $items_to_delete[] = $row['id'];
        }
        
        if (empty($items_to_delete)) {
            throw new Exception("No items to sell");
        }
        
        // Usuń wszystkie przedmioty
        $placeholders = implode(',', array_fill(0, count($items_to_delete), '?'));
        $delete_query = "DELETE FROM inventory WHERE id IN ($placeholders) AND user_id = ?";
        
        $delete_stmt = $conn->prepare($delete_query);
        
        // Przygotuj parametry do bind_param
        $types = str_repeat('i', count($items_to_delete)) . 'i';
        $params = array_merge($items_to_delete, [$user_id]);
        
        $delete_stmt->bind_param($types, ...$params);
        
        if ($delete_stmt->execute()) {
            if ($delete_stmt->affected_rows > 0) {
                // Aktualizuj portfel użytkownika
                $update_query = "UPDATE users SET wallet = wallet + ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("di", $total_value, $user_id);
                
                if ($update_stmt->execute()) {
                    $conn->commit();
                    $_SESSION['wallet'] += $total_value;
                    $_SESSION['success_message'] = "All items sold successfully for total $" . $total_value;
                } else {
                    throw new Exception("Error updating wallet");
                }
            } else {
                throw new Exception("No items were deleted");
            }
        } else {
            throw new Exception("Error deleting items");
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Sell all items error: " . $e->getMessage());
        $_SESSION['error_message'] = "Error selling items. Please try again.";
    }
    
} else {
    // Sprzedaj pojedynczy przedmiot (oryginalny kod)
    $item_id = (int)$item_id;
    
    $p_query = "SELECT i.sell_price FROM items AS i 
                INNER JOIN inventory AS inv ON i.id = inv.item_id 
                WHERE inv.id = ? AND inv.user_id = ?";
    
    $stmt = $conn->prepare($p_query);
    $stmt->bind_param("ii", $item_id, $user_id);
    $stmt->execute();
    $p_result = $stmt->get_result();
    
    if ($p_result->num_rows > 0) {
        $p = $p_result->fetch_assoc();
        $ret_price = $p['sell_price'];
        
        $conn->begin_transaction();
        
        try {
            $delete_query = "DELETE FROM inventory WHERE id = ? AND user_id = ?";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->bind_param("ii", $item_id, $user_id);
            
            if ($delete_stmt->execute()) {
                if ($delete_stmt->affected_rows > 0) {
                    $update_query = "UPDATE users SET wallet = wallet + ? WHERE id = ?";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bind_param("di", $ret_price, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $conn->commit();
                        $_SESSION['wallet'] += $ret_price;
                        $_SESSION['success_message'] = "Item sold successfully for $" . $ret_price;
                    } else {
                        throw new Exception("Error updating wallet");
                    }
                } else {
                    throw new Exception("Item not found or already sold");
                }
            } else {
                throw new Exception("Error deleting item");
            }
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Sell item error: " . $e->getMessage());
            $_SESSION['error_message'] = "Error selling item. Please try again.";
        }
    } else {
        $_SESSION['error_message'] = "Item not found or you don't own this item";
    }
}

header("Location: /$back");
exit;
?>