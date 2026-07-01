<?php
/**
 * Genre Checklist Menu Generator
 * Reads channels.json, extracts genre_name values, creates a checklist menu,
 * and saves selected genres to selected.json
 */

// Configuration
$inputFile = 'data/channels.json';
$outputFile = 'selected.json';

// Check if input file exists
if (!file_exists($inputFile)) {
    die("Error: Input file '$inputFile' not found.\n");
}

// Read and decode JSON
$jsonContent = file_get_contents($inputFile);
$data = json_decode($jsonContent, true);

if ($data === null) {
    die("Error: Invalid JSON in '$inputFile'.\n");
}

if (!isset($data['channels']) || !is_array($data['channels'])) {
    die("Error: 'channels' key not found or invalid in JSON.\n");
}

// Extract unique genre names
$genres = [];
foreach ($data['channels'] as $channel) {
    if (isset($channel['genre_name']) && !empty($channel['genre_name'])) {
        $genres[$channel['genre_name']] = true;
    }
}

// Sort genres alphabetically
$genreList = array_keys($genres);
sort($genreList);

// Load previously selected genres if file exists
$selectedGenres = [];
if (file_exists($outputFile)) {
    $selectedContent = file_get_contents($outputFile);
    $selectedData = json_decode($selectedContent, true);
    if (is_array($selectedData)) {
        $selectedGenres = $selectedData;
    }
}

// Process form submission
$action = isset($_POST['action']) ? $_POST['action'] : '';
$message = '';

if ($action === 'save') {
    $selectedGenres = isset($_POST['genres']) ? $_POST['genres'] : [];
    
    // Save to file
    $result = file_put_contents($outputFile, json_encode($selectedGenres, JSON_PRETTY_PRINT));
    if ($result !== false) {
        $message = "✅ Selection saved successfully!";
    } else {
        $message = "❌ Error: Could not save to '$outputFile'.";
    }
}

if ($action === 'load') {
    if (file_exists($outputFile)) {
        $selectedContent = file_get_contents($outputFile);
        $selectedData = json_decode($selectedContent, true);
        if (is_array($selectedData)) {
            $selectedGenres = $selectedData;
            $message = "✅ Selection loaded successfully!";
        }
    }
}

// Handle exit action
if ($action === 'exit') {
    // Redirect to a specified page or close window
    $redirectUrl = isset($_POST['redirect']) ? $_POST['redirect'] : 'index.php';
    header("Location: $redirectUrl");
    exit;
}

// Detect dark mode preference from cookie or default to system
$darkMode = isset($_COOKIE['dark_mode']) ? $_COOKIE['dark_mode'] : 'auto';
if ($darkMode === 'auto') {
    // Will be handled by JS/CSS media query
    $darkClass = '';
} else {
    $darkClass = $darkMode === 'dark' ? 'dark-theme' : '';
}
?>
<!DOCTYPE html>
<html lang="en" class="<?php echo $darkClass; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Group</title>
    <style>
        /* ===== CSS Variables for Theming ===== */
        :root {
            /* Light theme (default) */
            --bg-body: #f0f2f5;
            --bg-container: #ffffff;
            --bg-stats: #f8fafc;
            --bg-grid: #fafafa;
            --bg-hover: #f0f0f0;
            --bg-badge: #e5e7eb;
            --bg-badge-primary: #dbeafe;
            --bg-badge-success: #d1fae5;
            --bg-input: #ffffff;
            
            --text-primary: #1a1a2e;
            --text-secondary: #6b7280;
            --text-light: #9ca3af;
            --text-inverse: #ffffff;
            --text-badge-primary: #1d4ed8;
            --text-badge-success: #065f46;
            
            --border-color: #e5e7eb;
            --border-input: #d1d5db;
            --border-focus: #3b82f6;
            
            --shadow-color: rgba(0,0,0,0.08);
            --shadow-focus: rgba(59, 130, 246, 0.1);
            
            --btn-primary-bg: #3b82f6;
            --btn-primary-hover: #2563eb;
            --btn-primary-shadow: rgba(59, 130, 246, 0.3);
            
            --btn-success-bg: #10b981;
            --btn-success-hover: #059669;
            --btn-success-shadow: rgba(16, 185, 129, 0.3);
            
            --btn-secondary-bg: #6b7280;
            --btn-secondary-hover: #4b5563;
            
            --btn-danger-bg: #ef4444;
            --btn-danger-hover: #dc2626;
            --btn-danger-shadow: rgba(239, 68, 68, 0.3);
            
            --btn-exit-bg: #8b5cf6;
            --btn-exit-hover: #7c3aed;
            --btn-exit-shadow: rgba(139, 92, 246, 0.3);
            
            --msg-success-bg: #d1fae5;
            --msg-success-text: #065f46;
            --msg-success-border: #a7f3d0;
            
            --msg-error-bg: #fee2e2;
            --msg-error-text: #991b1b;
            --msg-error-border: #fca5a5;
            
            --scrollbar-track: #f1f1f1;
            --scrollbar-thumb: #c1c7cd;
            --scrollbar-thumb-hover: #a0a8b0;
            
            --transition-speed: 0.3s;
        }

        /* ===== Dark Theme ===== */
        .dark-theme {
            --bg-body: #0f1117;
            --bg-container: #1a1d27;
            --bg-stats: #242836;
            --bg-grid: #1e222d;
            --bg-hover: #2a2f3d;
            --bg-badge: #2d3342;
            --bg-badge-primary: #1e3a5f;
            --bg-badge-success: #1a3a2e;
            --bg-input: #1a1d27;
            
            --text-primary: #e8edf5;
            --text-secondary: #9aa4b8;
            --text-light: #6b7a8f;
            --text-inverse: #0f1117;
            --text-badge-primary: #7bb3f0;
            --text-badge-success: #5fc99c;
            
            --border-color: #2d3342;
            --border-input: #3a4052;
            --border-focus: #5b8def;
            
            --shadow-color: rgba(0,0,0,0.4);
            --shadow-focus: rgba(59, 130, 246, 0.2);
            
            --btn-primary-bg: #2b6ed7;
            --btn-primary-hover: #3b82f6;
            --btn-primary-shadow: rgba(43, 110, 215, 0.4);
            
            --btn-success-bg: #0d9e6c;
            --btn-success-hover: #10b981;
            --btn-success-shadow: rgba(13, 158, 108, 0.4);
            
            --btn-secondary-bg: #4a5268;
            --btn-secondary-hover: #5d677f;
            
            --btn-danger-bg: #c73b3b;
            --btn-danger-hover: #ef4444;
            --btn-danger-shadow: rgba(199, 59, 59, 0.4);
            
            --btn-exit-bg: #7c3aed;
            --btn-exit-hover: #8b5cf6;
            --btn-exit-shadow: rgba(124, 58, 237, 0.4);
            
            --msg-success-bg: #1a3a2e;
            --msg-success-text: #6ee7b7;
            --msg-success-border: #2d6b4f;
            
            --msg-error-bg: #3a1a1a;
            --msg-error-text: #f87171;
            --msg-error-border: #6b2d2d;
            
            --scrollbar-track: #1a1d27;
            --scrollbar-thumb: #3a4052;
            --scrollbar-thumb-hover: #4a5268;
        }

        /* ===== Base Styles ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--bg-body);
            padding: 20px;
            min-height: 100vh;
            color: var(--text-primary);
            transition: background var(--transition-speed), color var(--transition-speed);
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: var(--bg-container);
            border-radius: 16px;
            box-shadow: 0 4px 20px var(--shadow-color);
            padding: 30px;
            transition: background var(--transition-speed), box-shadow var(--transition-speed);
        }
        
        /* ===== Header ===== */
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        
        h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .theme-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--bg-stats);
            padding: 6px 12px 6px 16px;
            border-radius: 30px;
            border: 1px solid var(--border-color);
            cursor: pointer;
            font-size: 13px;
            color: var(--text-secondary);
            transition: all var(--transition-speed);
            user-select: none;
            flex-shrink: 0;
        }
        
        .theme-toggle:hover {
            background: var(--bg-hover);
            border-color: var(--border-input);
        }
        
        .theme-toggle .icon {
            font-size: 18px;
            line-height: 1;
        }
        
        .theme-toggle .label {
            font-weight: 500;
        }
        
        .subtitle {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid var(--border-color);
            transition: color var(--transition-speed), border-color var(--transition-speed);
        }
        
        /* ===== Stats ===== */
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .stat-box {
            background: var(--bg-stats);
            padding: 12px 18px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            transition: background var(--transition-speed), border-color var(--transition-speed);
        }
        
        .stat-box .label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-box .value {
            font-size: 20px;
            font-weight: 600;
            color: var(--text-primary);
        }
        
        /* ===== Messages ===== */
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .message.success {
            background: var(--msg-success-bg);
            color: var(--msg-success-text);
            border: 1px solid var(--msg-success-border);
        }
        
        .message.error {
            background: var(--msg-error-bg);
            color: var(--msg-error-text);
            border: 1px solid var(--msg-error-border);
        }
        
        /* ===== Search ===== */
        .search-box {
            margin-bottom: 16px;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border-input);
            border-radius: 8px;
            font-size: 14px;
            background: var(--bg-input);
            color: var(--text-primary);
            transition: border-color 0.2s, background var(--transition-speed), color var(--transition-speed);
        }
        
        .search-box input:focus {
            outline: none;
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px var(--shadow-focus);
        }
        
        .search-box input::placeholder {
            color: var(--text-light);
        }
        
        /* ===== Genre Grid ===== */
        .genre-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 10px;
            max-height: 500px;
            overflow-y: auto;
            padding: 4px;
            margin-bottom: 20px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background: var(--bg-grid);
            transition: background var(--transition-speed), border-color var(--transition-speed);
        }
        
        .genre-grid::-webkit-scrollbar {
            width: 8px;
        }
        
        .genre-grid::-webkit-scrollbar-track {
            background: var(--scrollbar-track);
            border-radius: 4px;
        }
        
        .genre-grid::-webkit-scrollbar-thumb {
            background: var(--scrollbar-thumb);
            border-radius: 4px;
        }
        
        .genre-grid::-webkit-scrollbar-thumb:hover {
            background: var(--scrollbar-thumb-hover);
        }
        
        .genre-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 6px;
            transition: background 0.15s;
            cursor: pointer;
        }
        
        .genre-item:hover {
            background: var(--bg-hover);
        }
        
        .genre-item input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--border-focus);
            cursor: pointer;
            flex-shrink: 0;
        }
        
        .genre-item label {
            font-size: 13px;
            color: var(--text-primary);
            cursor: pointer;
            user-select: none;
            word-break: break-word;
            transition: color var(--transition-speed);
        }
        
        .genre-item .count {
            margin-left: auto;
            font-size: 11px;
            color: var(--text-light);
            background: var(--bg-badge);
            padding: 1px 8px;
            border-radius: 12px;
            flex-shrink: 0;
            transition: background var(--transition-speed), color var(--transition-speed);
        }
        
        /* ===== Selection Info ===== */
        .selection-info {
            font-size: 13px;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 12px;
            transition: color var(--transition-speed);
        }
        
        .badge {
            background: var(--bg-badge);
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: background var(--transition-speed), color var(--transition-speed);
            color: var(--text-secondary);
        }
        
        .badge:hover {
            opacity: 0.8;
        }
        
        .badge-primary {
            background: var(--bg-badge-primary);
            color: var(--text-badge-primary);
        }
        
        .badge-success {
            background: var(--bg-badge-success);
            color: var(--text-badge-success);
        }
        
        /* ===== Buttons ===== */
        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            padding-top: 16px;
            border-top: 1px solid var(--border-color);
            transition: border-color var(--transition-speed);
        }
        
        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--text-inverse);
        }
        
        .btn-primary {
            background: var(--btn-primary-bg);
        }
        
        .btn-primary:hover {
            background: var(--btn-primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px var(--btn-primary-shadow);
        }
        
        .btn-success {
            background: var(--btn-success-bg);
        }
        
        .btn-success:hover {
            background: var(--btn-success-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px var(--btn-success-shadow);
        }
        
        .btn-secondary {
            background: var(--btn-secondary-bg);
        }
        
        .btn-secondary:hover {
            background: var(--btn-secondary-hover);
            transform: translateY(-1px);
        }
        
        .btn-danger {
            background: var(--btn-danger-bg);
        }
        
        .btn-danger:hover {
            background: var(--btn-danger-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px var(--btn-danger-shadow);
        }
        
        .btn-outline {
            background: transparent;
            color: var(--text-secondary);
            border: 1px solid var(--border-input);
        }
        
        .btn-outline:hover {
            background: var(--bg-hover);
            border-color: var(--text-light);
        }
        
        .btn-exit {
            background: var(--btn-exit-bg);
        }
        
        .btn-exit:hover {
            background: var(--btn-exit-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px var(--btn-exit-shadow);
        }
        
        /* ===== Footer Note ===== */
        .footer-note {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--border-color);
            font-size: 13px;
            color: var(--text-secondary);
            transition: border-color var(--transition-speed), color var(--transition-speed);
        }
        
        .footer-note code {
            background: var(--bg-stats);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            color: var(--text-primary);
            transition: background var(--transition-speed), color var(--transition-speed);
        }
        
        /* ===== Responsive ===== */
        @media (max-width: 600px) {
            .container {
                padding: 16px;
            }
            
            .genre-grid {
                grid-template-columns: 1fr;
                max-height: 400px;
            }
            
            .stats {
                flex-direction: column;
                gap: 8px;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
                text-align: center;
            }
            
            .header-row {
                flex-direction: column;
                align-items: stretch;
            }
            
            .theme-toggle {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header with Dark Mode Toggle -->
        <div class="header-row">
            <h1>📋 Group Selection</h1>
            <div class="theme-toggle" onclick="toggleTheme()" title="Toggle dark/light theme">
                <span class="icon" id="themeIcon"><?php echo $darkClass === 'dark-theme' ? '🌙' : '☀️'; ?></span>
                <span class="label" id="themeLabel"><?php echo $darkClass === 'dark-theme' ? 'Dark' : 'Light'; ?></span>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="genreForm">
            <input type="hidden" name="action" id="actionInput" value="">
            <input type="hidden" name="redirect" id="redirectInput" value="">
            
            <div class="stats">
                <div class="stat-box">
                    <div class="label">Total Genres</div>
                    <div class="value"><?php echo count($genreList); ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">Selected</div>
                    <div class="value" id="selectedCount"><?php echo count($selectedGenres); ?></div>
                </div>
                <div class="stat-box">
                    <div class="label">Total Channels</div>
                    <div class="value"><?php echo number_format($data['count'] ?? count($data['channels'])); ?></div>
                </div>
            </div>
            
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Search group..." onkeyup="filterGenres()">
            </div>
            
            <div class="selection-info">
                <span>Selection:</span>
                <span id="selectionText"><?php echo count($selectedGenres); ?> selected</span>
                <span class="badge badge-primary" onclick="toggleAll(true)" style="cursor:pointer;">Select All</span>
                <span class="badge" onclick="toggleAll(false)" style="cursor:pointer;">Deselect All</span>
                <span class="badge badge-success" onclick="toggleAllBySearch(true)" style="cursor:pointer;">Select Search Results</span>
                <span class="badge" onclick="toggleAllBySearch(false)" style="cursor:pointer;">Deselect Search Results</span>
            </div>
            
            <div class="genre-grid" id="genreGrid">
                <?php
                $channelCount = [];
                foreach ($data['channels'] as $channel) {
                    if (isset($channel['genre_name'])) {
                        $key = $channel['genre_name'];
                        $channelCount[$key] = ($channelCount[$key] ?? 0) + 1;
                    }
                }
                
                foreach ($genreList as $genre):
                    $count = $channelCount[$genre] ?? 0;
                    $checked = in_array($genre, $selectedGenres) ? 'checked' : '';
                ?>
                <div class="genre-item" data-genre="<?php echo htmlspecialchars($genre); ?>">
                    <input type="checkbox" 
                           name="genres[]" 
                           value="<?php echo htmlspecialchars($genre); ?>" 
                           id="genre_<?php echo md5($genre); ?>"
                           <?php echo $checked; ?>
                           onchange="updateSelection()">
                    <label for="genre_<?php echo md5($genre); ?>">
                        <?php echo htmlspecialchars($genre); ?>
                    </label>
                    <span class="count"><?php echo $count; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="actions">
                <button type="button" class="btn btn-primary" onclick="submitAction('save')">
                    💾 Save Selection
                </button>
                <button type="button" class="btn btn-success" onclick="submitAction('load')">
                    📂 Load Saved
                </button>
                <button type="button" class="btn btn-secondary" onclick="window.location.reload()">
                    🔄 Reset
                </button>
                <button type="button" class="btn btn-danger" onclick="clearSelection()">
                    🗑️ Clear All
                </button>
                <button type="button" class="btn btn-exit" onclick="exitPage()">
                    🚪 Exit
                </button>
            </div>
        </form>
        
        <div class="footer-note">
            <strong>Note:</strong> Selected genres are saved to <code>selected.json</code>
        </div>
    </div>

    <script>
        // ===== Selection Management =====
        function updateSelection() {
            const checkboxes = document.querySelectorAll('input[name="genres[]"]');
            const checked = document.querySelectorAll('input[name="genres[]"]:checked');
            document.getElementById('selectedCount').textContent = checked.length;
            document.getElementById('selectionText').textContent = checked.length + ' selected';
        }
        
        function filterGenres() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const items = document.querySelectorAll('.genre-item');
            
            items.forEach(item => {
                const genre = item.getAttribute('data-genre').toLowerCase();
                if (genre.includes(query)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }
        
        function toggleAll(select) {
            const checkboxes = document.querySelectorAll('input[name="genres[]"]');
            checkboxes.forEach(cb => {
                const item = cb.closest('.genre-item');
                if (item && item.style.display !== 'none') {
                    cb.checked = select;
                }
            });
            updateSelection();
        }
        
        function toggleAllBySearch(select) {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const items = document.querySelectorAll('.genre-item');
            
            items.forEach(item => {
                const genre = item.getAttribute('data-genre').toLowerCase();
                if (genre.includes(query)) {
                    const cb = item.querySelector('input[type="checkbox"]');
                    if (cb) cb.checked = select;
                }
            });
            updateSelection();
        }
        
        function clearSelection() {
            if (confirm('Are you sure you want to clear all selections?')) {
                const checkboxes = document.querySelectorAll('input[name="genres[]"]');
                checkboxes.forEach(cb => cb.checked = false);
                updateSelection();
            }
        }
        
        function submitAction(action) {
            document.getElementById('actionInput').value = action;
            document.getElementById('genreForm').submit();
        }
        
        function exitPage() {
            const redirectUrl = 'index.php';
            document.getElementById('redirectInput').value = redirectUrl;
            document.getElementById('actionInput').value = 'exit';
            document.getElementById('genreForm').submit();
        }
        
        // ===== Dark Theme Toggle =====
        function toggleTheme() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark-theme');
            const icon = document.getElementById('themeIcon');
            const label = document.getElementById('themeLabel');
            
            if (isDark) {
                html.classList.remove('dark-theme');
                icon.textContent = '☀️';
                label.textContent = 'Light';
                document.cookie = 'dark_mode=light; path=/; max-age=31536000';
            } else {
                html.classList.add('dark-theme');
                icon.textContent = '🌙';
                label.textContent = 'Dark';
                document.cookie = 'dark_mode=dark; path=/; max-age=31536000';
            }
        }
        
        // ===== Auto-detect system preference =====
        function detectSystemTheme() {
            // Only apply if cookie is not set or set to 'auto'
            const cookieValue = document.cookie.replace(/(?:(?:^|.*;\s*)dark_mode\s*\=\s*([^;]*).*$)|^.*$/, "$1");
            if (cookieValue === 'auto' || cookieValue === '') {
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.classList.add('dark-theme');
                    const icon = document.getElementById('themeIcon');
                    const label = document.getElementById('themeLabel');
                    if (icon) icon.textContent = '🌙';
                    if (label) label.textContent = 'Dark';
                }
            }
        }
        
        // ===== Initialize =====
        document.addEventListener('DOMContentLoaded', function() {
            updateSelection();
            detectSystemTheme();
        });
    </script>
</body>
</html>