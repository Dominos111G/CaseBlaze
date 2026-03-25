<?php
require_once 'config.php';
require_once 'connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$crate_id = isset($_POST['crate_id']) ? (int)$_POST['crate_id'] : 0;
$user_id = $_SESSION['user_id'];

// Sprawdź czy skrzynka istnieje i jest widoczna
$crate_query = "SELECT * FROM crates WHERE id = ? AND visible = 1";
$stmt = $conn->prepare($crate_query);
$stmt->bind_param('i', $crate_id);
$stmt->execute();
$crate_result = $stmt->get_result();

if ($crate_result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Crate not found']);
    exit;
}

$crate = $crate_result->fetch_assoc();

// Sprawdź stan portfela użytkownika
$user_query = "SELECT wallet FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// DLA DARMOWYCH SKRZYNEK - sprawdź timestampy
if ($crate['price'] == 0) {
    // Pobierz timestampy użytkownika
    $time_query = "SELECT daily_crate, weekly_crate FROM users WHERE id = ?";
    $stmt = $conn->prepare($time_query);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $time_result = $stmt->get_result();
    $time_fetch = $time_result->fetch_assoc();
    
    if ($crate['name'] == "Daily Case") {
        // Sprawdź czy to nie jest domyślna wartość
        if ($time_fetch['daily_crate'] != '0000-00-00 00:00:00') {
            $last_opened = strtotime($time_fetch['daily_crate']);
            if ($last_opened > time()) {
                $remaining = $last_opened - time();
                $hours = floor($remaining / 3600);
                $minutes = floor(($remaining % 3600) / 60);
                echo json_encode([
                    'success' => false, 
                    'error' => "Daily crate already opened. Next in {$hours}h {$minutes}m"
                ]);
                exit;
            }
        }
    } elseif ($crate['name'] == 'Weekly Case') {
        // Sprawdź czy to nie jest domyślna wartość
        if ($time_fetch['weekly_crate'] != '0000-00-00 00:00:00') {
            $last_opened = strtotime($time_fetch['weekly_crate']);
            if ($last_opened > time()) {
                $remaining = $last_opened - time();
                $days = floor($remaining / (24 * 3600));
                $hours = floor(($remaining % (24 * 3600)) / 3600);
                echo json_encode([
                    'success' => false, 
                    'error' => "Weekly crate already opened. Next in {$days}d {$hours}h"
                ]);
                exit;
            }
        }
    }
} 
// DLA PŁATNYCH SKRZYNEK - sprawdź czy użytkownik ma wystarczająco środków
elseif ($user['wallet'] < $crate['price']) {
    echo json_encode(['success' => false, 'error' => 'Insufficient funds']);
    exit;
}

// Pobierz wszystkie przedmioty ze skrzynki z ich wagami
$items_query = "
    SELECT DISTINCT i.*, e.rate as exterior_rate, q.rate as quality_rate 
    FROM crate_item ci
    INNER JOIN items i ON ci.item_id = i.id
    INNER JOIN exteriors e ON i.exterior_id = e.id
    INNER JOIN quality q ON i.quality_id = q.id
    WHERE ci.crate_id = ?
";
$stmt = $conn->prepare($items_query);
$stmt->bind_param('i', $crate_id);
$stmt->execute();
$items_result = $stmt->get_result();

$items = [];
while ($item = $items_result->fetch_assoc()) {
    // Oblicz wagę przedmiotu (rzadkość) na podstawie ceny, exterior i quality
    $base_weight = 1000; // Bazowa waga
    
    // Cena wpływa na rzadkość (droższe = rzadsze)
    $price_factor = $item['market_price'] > 0 ? 100 / $item['market_price'] : 100;
    $price_factor = min(max($price_factor, 0.1), 10); // Ogranicz do zakresu 0.1-10
    
    // Exterior rate (wyższy rate = rzadszy)
    $exterior_factor = 1 / ($item['exterior_rate'] ?: 1);
    
    // Quality rate (wyższy rate = rzadszy)
    $quality_factor = 1 / ($item['quality_rate'] ?: 1);
    
    // Oblicz końcową wagę (im mniejsza, tym rzadszy przedmiot)
    $weight = $base_weight * $price_factor * $exterior_factor * $quality_factor;
    
    $items[] = [
        'id' => $item['id'],
        'name' => $item['name'],
        'description' => $item['description'],
        'market_price' => (float)$item['market_price'],
        'sell_price' => (float)$item['sell_price'],
        'img' => $item['img'],
        'weight' => max(1, round($weight)) // Minimalna waga to 1
    ];
}

if (empty($items)) {
    echo json_encode(['success' => false, 'error' => 'No items in this crate']);
    exit;
}

// Losowanie przedmiotu z uwzględnieniem wag
$total_weight = array_sum(array_column($items, 'weight'));
$random = mt_rand(1, $total_weight);

$cumulative_weight = 0;
$selected_item = null;

foreach ($items as $item) {
    $cumulative_weight += $item['weight'];
    if ($random <= $cumulative_weight) {
        $selected_item = $item;
        break;
    }
}

// Jeśli coś poszło nie tak, wybierz pierwszy przedmiot
if (!$selected_item) {
    $selected_item = $items[0];
}

// Rozpocznij transakcję
$conn->begin_transaction();

try {
    // Odejmij pieniądze jeśli skrzynka nie jest darmowa
    if ($crate['price'] > 0) {
        $new_wallet = $user['wallet'] - $crate['price'];
        $update_wallet = "UPDATE users SET wallet = ? WHERE id = ?";
        $stmt = $conn->prepare($update_wallet);
        $stmt->bind_param('di', $new_wallet, $user_id);
        $stmt->execute();
    }
    
    // DLA DARMOWYCH SKRZYNEK - zaktualizuj datę (użyj DATETIME)
    if ($crate['price'] == 0) {
        if ($crate['name'] == "Daily Case") {
            // Ustaw datę na jutro o tej samej godzinie
            $next_date = date('Y-m-d H:i:s', strtotime('+1 day'));
            $update_query = "UPDATE users SET daily_crate = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param('si', $next_date, $user_id);
            $stmt->execute();
            
        } elseif ($crate['name'] == 'Weekly Case') {
            // Ustaw datę na za 7 dni o tej samej godzinie
            $next_date = date('Y-m-d H:i:s', strtotime('+7 days'));
            $update_query = "UPDATE users SET weekly_crate = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param('si', $next_date, $user_id);
            $stmt->execute();
        }
    }
    
    // Dodaj przedmiot do ekwipunku
    $add_to_inventory = "INSERT INTO inventory (user_id, item_id) VALUES (?, ?)";
    $stmt = $conn->prepare($add_to_inventory);
    $stmt->bind_param('ii', $user_id, $selected_item['id']);
    $stmt->execute();
    
    // Zatwierdź transakcję
    $conn->commit();
    
    // Pobierz zaktualizowaną datę dla darmowej skrzynki
    $next_timestamp = 0;
    if ($crate['price'] == 0) {
        if ($crate['name'] == "Daily Case") {
            $query = "SELECT daily_crate FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $next_timestamp = strtotime($row['daily_crate']);
        } elseif ($crate['name'] == 'Weekly Case') {
            $query = "SELECT weekly_crate FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $next_timestamp = strtotime($row['weekly_crate']);
        }
    }
    
} catch (Exception $e) {
    // Wycofaj transakcję w przypadku błędu
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    exit;
}

// Pobierz zaktualizowany stan portfela
$user_query = "SELECT wallet FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$updated_user = $user_result->fetch_assoc();

// Generuj listę przedmiotów do animacji
$animation_items = [];
$total_items = 80;
$winning_index = 40; // Środkowa pozycja

// Pobierz WSZYSTKIE przedmioty ze skrzynki do puli (z uwzględnieniem wag dla częstotliwości występowania)
$all_stmt = $conn->prepare("
    SELECT DISTINCT i.*, e.rate as exterior_rate, q.rate as quality_rate 
    FROM crate_item ci 
    JOIN items i ON ci.item_id = i.id
    INNER JOIN exteriors e ON i.exterior_id = e.id
    INNER JOIN quality q ON i.quality_id = q.id
    WHERE ci.crate_id = ?
");
$all_stmt->bind_param('i', $crate_id);
$all_stmt->execute();
$pool_result = $all_stmt->get_result();
$pool_items = [];

// Przygotuj pulę przedmiotów z wagami do losowania w animacji
while ($row = $pool_result->fetch_assoc()) {
    // Oblicz wagę dla animacji (użyj tych samych wag co przy losowaniu)
    $base_weight = 1000;
    $price_factor = $row['market_price'] > 0 ? 100 / $row['market_price'] : 100;
    $price_factor = min(max($price_factor, 0.1), 10);
    $exterior_factor = 1 / ($row['exterior_rate'] ?: 1);
    $quality_factor = 1 / ($row['quality_rate'] ?: 1);
    $weight = max(1, round($base_weight * $price_factor * $exterior_factor * $quality_factor));
    
    for ($i = 0; $i < $weight; $i++) {
        $pool_items[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'market_price' => (float)$row['market_price'],
            'sell_price' => (float)$row['sell_price'],
            'img' => $row['img']
        ];
    }
}

// Generuj listę przedmiotów do animacji (80 pozycji)
for ($i = 0; $i < $total_items; $i++) {
    if ($i === $winning_index) {
        // Na pozycji wygrywającej umieść faktycznie wylosowany przedmiot
        $animation_items[] = $selected_item;
    } else {
        // Na pozostałych pozycjach losuj przedmioty z puli z uwzględnieniem wag
        $random_index = array_rand($pool_items);
        $animation_items[] = $pool_items[$random_index];
    }
}

echo json_encode([
    'success' => true,
    'selected_item' => $selected_item,
    'animation_items' => $animation_items,
    'winning_position' => $winning_index,
    'chest_price' => (float)$crate['price'],
    'new_balance' => (float)$updated_user['wallet'],
    'next_available' => $next_timestamp ?? 0
]);
?>