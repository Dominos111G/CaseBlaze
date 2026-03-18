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
                }
                ?>
            </div>

            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']): ?>
                <div class="opening-section">
                    <button class="open-btn <?php echo $crate['price'] == 0 ? 'free' : ''; ?>" 
                            id="openCrateBtn" 
                            data-crate-id="<?php echo $crate['id']; ?>"
                            data-price="<?php echo $crate['price']; ?>"
                            <?php echo ($user['wallet'] < $crate['price'] && $crate['price'] > 0) ? 'disabled' : ''; ?>>
                        <?php 
                        if ($crate['price'] == 0) {
                            echo 'OTWÓRZ DARMOWĄ SKRZYNKĘ';
                        } else {
                            echo 'OTWÓRZ ZA ' . number_format($crate['price'], 2) . ' vPLN';
                        }
                        ?>
                    </button>

                    <div class="opening-container" id="openingContainer" style="display: none;">
                        <div class="items-slider" id="itemsSlider"></div>
                        <div class="slider-controls">
                            <button class="slider-btn" id="prevBtn" disabled>←</button>
                            <button class="slider-btn" id="nextBtn" disabled>→</button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="error-message">
                    Zaloguj się, aby otworzyć tę skrzynkę
                </div>
            <?php endif; ?>

            <h2 style="margin: 40px 0 20px; color: #333;">Przedmioty w skrzynce</h2>
            
            <?php
            $i_query = "SELECT i.*, e.name as exterior_name, q.name as quality_name 
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
                            <div class="item-image">
                            <img class="item-image" src="/img/items/<?php echo $ir['img']; ?>" alt="Item Image">
                            </div>
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
        let currentAnimationItems = [];
        let winningPosition = 0;
        let animationInterval = null;
        let currentPosition = 0;
        const slider = document.getElementById('itemsSlider');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const openBtn = document.getElementById('openCrateBtn');
        const openingContainer = document.getElementById('openingContainer');
        const loading = document.getElementById('loading');
        const resultModal = document.getElementById('resultModal');
        const resultItem = document.getElementById('resultItem');
        
        function startAnimation(items, winningPos) {
            currentAnimationItems = items;
            winningPosition = winningPos;
            
            // Wyświetl slider
            openingContainer.style.display = 'block';
            
            // Stwórz elementy slidera
            slider.innerHTML = '';
            items.forEach((item, index) => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'slider-item' + (index === winningPosition ? ' winning-item' : '');
                itemDiv.innerHTML = `
                    <img class="item-image" src="/img/items/${item.img}" alt="Item Image">
                    <div class="item-name">${item.name}</div>
                    <div class="item-price">${item.market_price.toFixed(2)} vPLN</div>
                `;
                slider.appendChild(itemDiv);
            });

            // Wyśrodkuj na wygrywającym przedmiocie
            setTimeout(() => {
                const itemWidth = 220; // szerokość + gap
                const targetScroll = winningPosition * itemWidth - (slider.clientWidth / 2) + 100;
                slider.scrollLeft = targetScroll;
                currentPosition = winningPosition;
            }, 100);

            // Włącz przyciski
            prevBtn.disabled = false;
            nextBtn.disabled = false;
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

        prevBtn.addEventListener('click', () => moveSlider(-1));
        nextBtn.addEventListener('click', () => moveSlider(1));

        if (openBtn) {
            openBtn.addEventListener('click', async function() {
                openBtn.disabled = true;
                const crateId = this.dataset.crateId;
                const price = parseFloat(this.dataset.price);

                // Sprawdź czy użytkownik ma wystarczająco środków
                <?php if (isset($user)): ?>
                if (price > 0 && <?php echo $user['wallet']; ?> < price) {
                    alert('Nie masz wystarczających środków na portfelu!');
                    return;
                }
                <?php endif; ?>

                // Pokaż loading
                loading.style.display = 'flex';

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

                        // Zablokuj przycisk jeśli brak środków
                        if (data.new_balance < price && price > 0) {
                            openBtn.disabled = true;
                        }

                        // Rozpocznij animację
                        startAnimation(data.animation_items, data.winning_position);

                        // Ukryj loading
                        loading.style.display = 'none';

                        // Pokaż wynik po krótkim opóźnieniu
                        setTimeout(() => {
                            showResult(data.selected_item);
                            openBtn.disabled = false;
                        }, 1500);
                    } else {
                        loading.style.display = 'none';
                        alert('Błąd: ' + (data.error || 'Nie udało się otworzyć skrzynki'));
                    }
                } catch (error) {
                    loading.style.display = 'none';
                    console.error('Error:', error);
                    alert('Wystąpił błąd podczas otwierania skrzynki');
                }
            });
        }
    </script>
</body>
</html>