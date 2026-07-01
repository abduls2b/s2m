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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Group</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f0f2f5;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 30px;
        }
        
        h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .subtitle {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .stat-box {
            background: #f8fafc;
            padding: 12px 18px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        
        .stat-box .label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-box .value {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a2e;
        }
        
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .message.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .message.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        
        .search-box {
            margin-bottom: 16px;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .genre-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 10px;
            max-height: 500px;
            overflow-y: auto;
            padding: 4px;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fafafa;
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
            background: #f0f0f0;
        }
        
        .genre-item input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #3b82f6;
            cursor: pointer;
            flex-shrink: 0;
        }
        
        .genre-item label {
            font-size: 13px;
            color: #1a1a2e;
            cursor: pointer;
            user-select: none;
            word-break: break-word;
        }
        
        .genre-item .count {
            margin-left: auto;
            font-size: 11px;
            color: #9ca3af;
            background: #f3f4f6;
            padding: 1px 8px;
            border-radius: 12px;
            flex-shrink: 0;
        }
        
        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
        }
        
        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4b5563;
            transform: translateY(-1px);
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        
        .btn-outline {
            background: transparent;
            color: #6b7280;
            border: 1px solid #d1d5db;
        }
        
        .btn-outline:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }
        
        /* Exit button specific styles */
        .btn-exit {
            background: #8b5cf6;
            color: white;
        }
        
        .btn-exit:hover {
            background: #7c3aed;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
        }
        
        .selection-info {
            font-size: 13px;
            color: #6b7280;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .badge {
            background: #e5e7eb;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-primary {
            background: #dbeafe;
            color: #1d4ed8;
        }
        
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        
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
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>
            📋 Group Selection
        </h1>
        
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
        
        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid #e5e7eb; font-size: 13px; color: #6b7280;">
            <strong>Note:</strong> Selected genres are saved to <code>selected.json</code>
        </div>
    </div>

    <script>
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
            // You can customize the redirect URL here
            // Option 1: Redirect to a specific page
            const redirectUrl = 'index.php'; // Change this to your desired page
            
            // Option 2: Try to close the window/tab (may not work in all browsers)
            // Uncomment the line below to try closing the window instead
            // window.close();
            
            // Option 3: Redirect to the previous page in history
            // Uncomment the line below to go back
            // window.history.back();
            
            // Set redirect and submit form
            document.getElementById('redirectInput').value = redirectUrl;
            document.getElementById('actionInput').value = 'exit';
            document.getElementById('genreForm').submit();
        }
        
        // Initialize selection count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateSelection();
        });
    </script>
</body>
</html>