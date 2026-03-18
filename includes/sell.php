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

$item_id = (int)$_POST['i_id'];
$user_id = (int)$_SESSION['user_id'];
$back = htmlspecialchars($_POST['back']);

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

header("Location: /$back");
exit;
?>