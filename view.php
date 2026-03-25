<?php 
include 'includes/config.php'; 
include 'includes/connect.php';
?>

<?php
if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    header('Location: /');
    exit;
} elseif (!isset($_GET['id'])) {
    header('Location: /');
    exit;
}

$id = (int)$_GET['id'];
$query = "SELECT * FROM crates WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: /');
    exit;
}

$crate = $result->fetch_assoc();

if ($crate['visible'] == 0) {
    header('Location: /');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaseBlaze - <?php echo htmlspecialchars($crate['name']); ?></title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/view.css">
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    
    <main>
        <div class="crate-container">
            <div class="crate-header">
                <h1><?php echo htmlspecialchars($crate['name']); ?></h1>
                <p><?php echo htmlspecialchars($crate['description']); ?></p>
                <?php 
                if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
                    $u_query = "SELECT wallet FROM users WHERE id = ?";
                    $stmt = $conn->prepare($u_query);
                    $stmt->bind_param('i', $_SESSION['user_id']);
                    $stmt->execute();
                    $u_result = $stmt->get_result();
                    $user = $u_result->fetch_assoc();
                    
                    // Pobierz daty dla darmowych skrzynek
                    $time_query = "SELECT daily_crate, weekly_crate FROM users WHERE id = ?";
                    $stmt = $conn->prepare($time_query);
                    $stmt->bind_param('i', $_SESSION['user_id']);
                    $stmt->execute();
                    $time_result = $stmt->get_result();
                    $time_fetch = $time_result->fetch_assoc();
                }
                ?>
            </div>

            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                <div class="opening-section">
                    <div class="button-container">
                        <?php
                        $is_disabled = false;
                        $button_text = '';
                        $next_timestamp = 0;
                        
                        if ($crate['price'] == 0) {
                            if ($crate['name'] == 'Daily Case') {
                                $last_opened = $time_fetch['daily_crate'];
                                
                                // Sprawdź czy to wartość domyślna (0000-00-00 00:00:00)
                                if ($last_opened == '0000-00-00 00:00:00') {
                                    $is_disabled = false;
                                    $button_text = 'Open Daily Case!';
                                    $next_timestamp = 0;
                                } else {
                                    $last_timestamp = strtotime($last_opened);
                                    $now = time();
                                    
                                    if ($last_timestamp <= $now) {
                                        $is_disabled = false;
                                        $button_text = 'Open Daily Case!';
                                        $next_timestamp = 0;
                                    } else {
                                        $is_disabled = true;
                                        $next_timestamp = $last_timestamp;
                                        $button_text = '<span class="timer-text" data-timestamp="' . $last_timestamp . '">Loading...</span>';
                                    }
                                }
                                
                            } elseif ($crate['name'] == 'Weekly Case') {
                                $last_opened = $time_fetch['weekly_crate'];
                                
                                // Sprawdź czy to wartość domyślna (0000-00-00 00:00:00)
                                if ($last_opened == '0000-00-00 00:00:00') {
                                    $is_disabled = false;
                                    $button_text = 'Open Weekly Case!';
                                    $next_timestamp = 0;
                                } else {
                                    $last_timestamp = strtotime($last_opened);
                                    $now = time();
                                    
                                    if ($last_timestamp <= $now) {
                                        $is_disabled = false;
                                        $button_text = 'Open Weekly Case!';
                                        $next_timestamp = 0;
                                    } else {
                                        $is_disabled = true;
                                        $next_timestamp = $last_timestamp;
                                        $button_text = '<span class="timer-text" data-timestamp="' . $last_timestamp . '">Loading...</span>';
                                    }
                                }
                            }
                            
                        } else {
                            $button_text = 'OPEN FOR ' . number_format($crate['price'], 2) . ' vPLN';
                            if ($user['wallet'] < $crate['price']) {
                                $is_disabled = true;
                            }
                        }
                        ?>
                        
                        <button class="open-btn <?php echo $crate['price'] == 0 ? 'free' : ''; ?>" 
                                id="openCrateBtn" 
                                data-crate-id="<?php echo $crate['id']; ?>"
                                data-price="<?php echo $crate['price']; ?>"
                                data-crate-name="<?php echo $crate['name']; ?>"
                                data-next-timestamp="<?php echo $next_timestamp; ?>"
                                <?php echo $is_disabled ? 'disabled' : ''; ?>>
                            <?php echo $button_text; ?>
                        </button>
                    </div>

                    <div class="opening-container" id="openingContainer" style="display: none;">
                        <div class="items-slider" id="itemsSlider"></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="error-message">
                    Log in to open this crate
                </div>
            <?php endif; ?>

            <h2 class="crate-header">Przedmioty w skrzynce</h2>
            
            <?php
            $i_query = "SELECT DISTINCT i.*, e.name as exterior_name, q.name as quality_name 
                       FROM crate_item ci 
                       INNER JOIN items i ON ci.item_id = i.id 
                       LEFT JOIN exteriors e ON i.exterior_id = e.id
                       LEFT JOIN quality q ON i.quality_id = q.id
                       WHERE ci.crate_id = ? 
                       ORDER BY i.market_price DESC";
            $stmt = $conn->prepare($i_query);
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $i_result = $stmt->get_result();

            if ($i_result->num_rows > 0):
            ?>
                <div class="items-grid">
                    <?php foreach ($i_result as $ir): ?>
                        <div class="crate-item">
                            <img class="item-image" src="/img/items/<?php echo $ir['img']; ?>" alt="Item Image">
                            <h4><?php echo htmlspecialchars($ir['name']); ?></h4>
                            <p>Jakość: <?php echo htmlspecialchars($ir['quality_name'] ?? 'Standard'); ?></p>
                            <p>Stan: <?php echo htmlspecialchars($ir['exterior_name'] ?? 'Standard'); ?></p>
                            <p class="price"><?php echo number_format($ir['market_price'], 2); ?> vPLN</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: red; text-align: center;">Brak przedmiotów w tej skrzynce.</p>
            <?php endif; ?>
        </div>
    </main>

    <!-- Modal z wynikiem -->
    <div class="result-modal" id="resultModal">
        <div class="result-content">
            <h2>Gratulacje!</h2>
            <div class="result-item" id="resultItem"></div>
            <div class="result-buttons">
                <button class="result-btn btn-primary" onclick="closeResult()">OK</button>
                <button class="result-btn btn-secondary" onclick="window.location.href='profile.php'">Idź do ekwipunku</button>
            </div>
        </div>
    </div>

    <!-- Loading overlay -->
    <div class="loading" id="loading">
        <div class="spinner"></div>
        <h2>Otwieranie skrzynki...</h2>
    </div>

    <script>
        // Elementy DOM
        const openBtn = document.getElementById('openCrateBtn');
        const slider = document.getElementById('itemsSlider');
        const openingContainer = document.getElementById('openingContainer');
        const loading = document.getElementById('loading');
        const resultModal = document.getElementById('resultModal');
        const resultItem = document.getElementById('resultItem');
        
        // Zmienne dla animacji
        let currentAnimationItems = [];
        let winningPosition = 0;
        let currentPosition = 0;
        
        // Zmienne dla timera
        let timerInterval = null;
        
        // Funkcja aktualizująca timer
        function updateTimer() {
            const now = Math.floor(Date.now() / 1000);
            const timerText = document.querySelector('.timer-text');
            
            if (!timerText) return;
            
            const targetTimestamp = parseInt(timerText.dataset.timestamp) || 0;
            
            if (targetTimestamp <= now) {
                // Czas minął - odblokuj przycisk i zmień tekst
                const crateName = openBtn.dataset.crateName;
                timerText.textContent = crateName === 'Daily Case' ? 'Open Daily Case!' : 'Open Weekly Case!';
                openBtn.disabled = false;
                
                // Zatrzymaj timer
                if (timerInterval) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                }
            } else {
                // Oblicz pozostały czas
                let remaining = targetTimestamp - now;
                let timeString = '';
                
                if (openBtn.dataset.crateName === 'Daily Case') {
                    const hours = Math.floor(remaining / 3600);
                    const minutes = Math.floor((remaining % 3600) / 60);
                    const seconds = remaining % 60;
                    timeString = `${hours}h ${minutes}m ${seconds}s`;
                } else {
                    const days = Math.floor(remaining / 86400);
                    const hours = Math.floor((remaining % 86400) / 3600);
                    const minutes = Math.floor((remaining % 3600) / 60);
                    timeString = `${days}d ${hours}h ${minutes}m`;
                }
                
                timerText.textContent = `Available in: ${timeString}`;
            }
        }
        
        // Inicjalizacja timera jeśli potrzebny
        if (openBtn && openBtn.dataset.nextTimestamp && parseInt(openBtn.dataset.nextTimestamp) > 0) {
            if (document.querySelector('.timer-text')) {
                updateTimer();
                timerInterval = setInterval(updateTimer, 1000);
            }
        }
        
        function startAnimation(items, winningPos) {
            const itemWidth = 160; // Szerokość elementu z marginesem
            const container = openingContainer;
            
            // WAŻNE: Najpierw pokaż kontener aby uzyskać poprawną szerokość
            container.style.display = 'flex';
            container.style.opacity = '0';
            
            // Pobierz szerokość DOPIERO po ustawieniu display: flex
            const containerWidth = container.offsetWidth;
            
            // Jeśli szerokość nadal wynosi 0 (awaryjnie), ustaw domyślną
            const finalWidth = containerWidth > 0 ? containerWidth : 800;
            
            // Przygotuj slider
            slider.style.transition = "none";
            slider.innerHTML = '';
            
            // Dodaj przedmioty
            items.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'slider-item';
                if (index === winningPos) {
                    // div.classList.add('winning-item');
                }
                div.innerHTML = `
                    <img src="/img/items/${item.img}" alt="${item.name}">
                    <div class="item-name">${item.name}</div>
                `;
                slider.appendChild(div);
            });
            
            // Wymuś ponowne obliczenie layoutu
            slider.offsetHeight;
            
            // Ustaw początkową pozycję - wszystkie przedmioty po prawej stronie
            const startOffset = finalWidth;
            slider.style.transform = `translateX(${startOffset}px)`;
            
            // Krótkie opóźnienie aby przeglądarka zarejestrowała pozycję startową
            setTimeout(() => {
                // Pokaż kontener
                container.style.opacity = '1';
                
                // Oblicz pozycję docelową
                const targetCenterX = (winningPos * itemWidth) + (itemWidth / 2);
                const targetOffset = (finalWidth / 2) - targetCenterX;
                
                // Dodaj losowe przesunięcie dla efektu
                const randomOffset = (Math.random() - 0.5) * 50;
                const finalOffset = targetOffset + randomOffset;
                
                // Uruchom animację
                slider.style.transition = "transform 6s cubic-bezier(0.2, 0.9, 0.3, 1.1)";
                slider.style.transform = `translateX(${finalOffset}px)`;
                
            }, 50);
            
            // Koniec animacji - pokaż wynik
            setTimeout(() => {
                const winningItem = items[winningPos];
                if (winningItem) {
                    showResult(winningItem);
                }
            }, 6200);
        }

        function moveSlider(direction) {
            const itemWidth = 220;
            const newPosition = currentPosition + direction;
            
            if (newPosition >= 0 && newPosition < currentAnimationItems.length) {
                currentPosition = newPosition;
                slider.scrollLeft = newPosition * itemWidth - (slider.clientWidth / 2) + 100;
                
                // Podświetl jeśli to wygrywający przedmiot
                document.querySelectorAll('.slider-item').forEach((item, index) => {
                    if (index === winningPosition) {
                        item.classList.add('winning-item');
                    } else {
                        item.classList.remove('winning-item');
                    }
                });
            }
        }

        function showResult(item) {
            resultItem.innerHTML = `
                <img class="item-image" src="/img/items/${item.img}" alt="Item Image">
                <h3>${item.name}</h3>
                <p>${item.description || ''}</p>
                <div class="result-price">${item.market_price.toFixed(2)} vPLN</div>
            `;
            resultModal.style.display = 'flex';
        }

        function closeResult() {
            resultModal.style.display = 'none';
        }

        // Otwieranie skrzynki
        if (openBtn) {
            openBtn.addEventListener('click', async function() {
                if (this.disabled) return;
                
                this.disabled = true;
                const crateId = this.dataset.crateId;
                const price = parseFloat(this.dataset.price);
                const crateName = this.dataset.crateName;

                // Pokaż loading
                loading.style.display = 'flex';
                
                // WAŻNE: Ukryj kontener z animacją przed nowym otwarciem
                openingContainer.style.display = 'none';
                openingContainer.style.opacity = '0';
                slider.style.transform = 'none';
                
                // Wyczyść poprzednią animację
                slider.innerHTML = '';

                try {
                    const formData = new FormData();
                    formData.append('crate_id', crateId);

                    const response = await fetch('includes/open.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Zaktualizuj saldo w interfejsie
                        const walletElement = document.getElementById('wallet');
                        if (walletElement) {
                            walletElement.textContent = data.new_balance.toFixed(2) + ' vPLN';
                        }
                        
                        // Zaktualizuj timestamp dla darmowych skrzynek
                        if (data.next_available) {
                            this.innerHTML = '<span class="timer-text" data-timestamp="' + data.next_available + '">Loading...</span>';
                            this.dataset.nextTimestamp = data.next_available;
                            
                            if (timerInterval) {
                                clearInterval(timerInterval);
                            }
                            updateTimer();
                            timerInterval = setInterval(updateTimer, 1000);
                        }
                        
                        // Ukryj loading
                        loading.style.display = 'none';
                        
                        // Zapisz wylosowany przedmiot
                        const actualWonItem = data.selected_item;
                        
                        // Małe opóźnienie przed animacją
                        setTimeout(() => {
                            startAnimation(data.animation_items, data.winning_position);
                            
                            // Zabezpieczenie - sprawdź po animacji
                            setTimeout(() => {
                                const displayedItem = data.animation_items[data.winning_position];
                                if (displayedItem && displayedItem.id !== actualWonItem.id) {
                                    console.warn('Animacja wyświetliła inny przedmiot! Poprawiam...');
                                    showResult(actualWonItem);
                                }
                            }, 6300);
                        }, 100);
                        
                        // Odblokuj przycisk po zakończeniu animacji
                        if (data.new_balance >= data.chest_price) {
                            setTimeout(() => {
                                this.disabled = false;
                            }, 7000);
                        }
                    } else {
                        loading.style.display = 'none';
                        alert('Błąd: ' + (data.error || 'Nie udało się otworzyć skrzynki'));
                        this.disabled = false;
                    }
                } catch (error) {
                    loading.style.display = 'none';
                    console.error('Error:', error);
                    alert('Wystąpił błąd podczas otwierania skrzynki');
                    this.disabled = false;
                }
            });
        }

        // Zatrzymaj timer przy odświeżeniu strony
        window.addEventListener('beforeunload', function() {
            if (timerInterval) {
                clearInterval(timerInterval);
            }
        });
    </script>
</body>
</html>