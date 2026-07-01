<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Live TV</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0;
      padding: 0;
      background: linear-gradient(135deg, #0d001e, #1c003f);
      color: #fff;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: 100vh;
    }
    .box {
      background: #1c0030;
      padding: 24px;
      border-radius: 20px;
      max-width: 400px;
      width: 90%;
      box-shadow: 0 0 20px #a000ff66;
      text-align: center;
      margin-top: 60px;
    }
    .box h2 {
      font-size: 1.8rem;
      background: linear-gradient(to right, #ff00cc, #3333ff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .steps {
      background: #0e0e0e;
      padding: 14px;
      border-radius: 12px;
      margin: 20px 0;
      text-align: left;
      font-size: 0.9rem;
      line-height: 1.5;
    }
    .steps li { margin-bottom: 8px; }

    .btn {
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1rem;
      font-weight: bold;
      color: #fff;
      margin-bottom: 20px;
      background: linear-gradient(to right, #00c6ff, #0072ff);
      position: relative;
      overflow: hidden;
    }

.copy-btn {
    background: var(--surface); border: 1px solid var(--border); color: var(--text-muted);
    cursor: pointer; font-size: 0.75rem; padding: 6px 10px; border-radius: var(--radius-sm);
    transition: all 0.2s; flex-shrink: 0; box-shadow: var(--shadow-sm);
}
.copy-btn:hover { color: var(--primary); border-color: var(--primary); background: var(--primary-bg); }


    .btn::before {
      content: '';
      position: absolute;
      top: 0;
      left: -75%;
      width: 50%;
      height: 100%;
      background: rgba(255, 255, 255, 0.3);
      transform: skewX(-20deg);
      animation: shine 2s infinite;
    }

    @keyframes shine {
      0% { left: -75%; }
      100% { left: 125%; }
    }

    .submit-btn {
      background: linear-gradient(to right, #00ffcc, #00b386);
      color: #000;
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1rem;
      font-weight: bold;
      transition: 0.3s;
    }

    input[type="password"] {
      padding: 12px;
      font-size: 1.1rem;
      width: 80%;
      margin: 10px 0;
      text-align: center;
      border-radius: 8px;
      border: none;
    }
    .error {
      margin-top: 12px;
      font-weight: bold;
      padding: 10px;
      border-radius: 8px;
      display: none;
      animation: shake 0.4s;
    }
    .error.active { display: block; }
    .error.red { background: #ff4d4d; color: #fff; }
    .error.orange { background: #ffaa00; color: #000; }

    .join {
      margin-top: 30px;
      background: #10001d;
      padding: 16px;
      border-radius: 14px;
    }
    .join a {
      text-decoration: none;
      color: #fff;
      display: inline-block;
      margin-top: 10px;
      padding: 10px 20px;
      background: linear-gradient(to right, #00c6ff, #0072ff);
      border-radius: 10px;
      font-weight: bold;
    }

    @keyframes shake {
      0% { transform: translateX(0); }
      25% { transform: translateX(-6px); }
      50% { transform: translateX(6px); }
      75% { transform: translateX(-4px); }
      100% { transform: translateX(0); }
    }
    @keyframes popup {
      0% { transform: scale(0.7); opacity: 0; }
      100% { transform: scale(1); opacity: 1; }
    }
    .success-dialog {
      position: fixed;
      top: 30%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: #1eff8b;
      color: #000;
      padding: 25px 40px;
      border-radius: 18px;
      font-size: 1.3rem;
      font-weight: bold;
      animation: popup 0.4s ease-out;
      box-shadow: 0 0 25px #0f08;
      z-index: 9999;
      border: 2px solid #00b46f;
    }

    .features {
      display: flex;
      flex-direction: column;
      gap: 16px;
      padding: 40px 20px;
      max-width: 600px;
      width: 100%;
    }
    .feature-card {
      background: #2d0044;
      padding: 18px;
      border-radius: 16px;
      transition: 0.3s;
      border: 1px solid #600080;
    }
    .feature-card:hover {
      background: #3a005a;
      transform: translateY(-4px) scale(1.02);
      box-shadow: 0 0 12px #ff00ff88;
    }
    .feature-card h3 {
      margin: 0;
      margin-bottom: 6px;
      font-size: 1.2rem;
      color: #fff;
    }
    .feature-card p {
      margin: 0;
      font-size: 0.95rem;
      color: #ccc;
    }
  </style>
</head>
<body>
</body>
</html>

<?php
$context = stream_context_create([
  'http' => [
    'timeout' => 20,
    'user_agent' => 'Mozilla/5.0'
  ]
]);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$requestUri = $_SERVER['REQUEST_URI'];
$scriptUrl = $protocol . $host . str_replace('category.php', 'data/channels.json', $requestUri);

$m3uContent = file_get_contents($scriptUrl);
$data = json_decode($m3uContent, true);

$channels = [];
$groups = [];
$current = null;

$data1 = file_get_contents('selected.json');
$group = json_decode($data1, true);

// Define allowed groups
$allowedGroups = $group;

foreach ($data['channels'] as $item) {
  $current = [
    'id' => $item['id'] ?? '',
    'group' => $item['genre_name'] ?? '',
    'logo' => $item['logo'] ?? '',
    'name' => $item['name'] ?? '' 
  ];

  // Check if group is in the allowed list and not already added
  if (in_array($current['group'], $allowedGroups) && !in_array($current['group'], $groups)) {
    $groups[] = $current['group'];
  }

  $channels[] = $current;
  $current = null;
}

// Sort groups alphabetically for better user experience
sort($groups);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Jiostar | Channels</title>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <style>
    .submit-theme-btn {
      text-decoration: none;
      padding: 7px 18px;
      border-radius: 99px;
      font-weight: 600;
      color: var(--text);
      border: 1px solid var(--btn);
      background: var(--card);
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
    }

    .submit-theme-btn:hover {
      background: var(--btn);
      color: #fff;
      transform: scale(1.05);
      box-shadow: 0 0 20px var(--btn);
    }

    .submit-theme-btn i {
      color: var(--btn);
      transition: 0.3s;
    }

    .submit-theme-btn:hover i {
      color: #fff;
    }

    :root {
      --bg: #0e0e0e;
      --card: #1a1a1a;
      --btn: #007bff;
      --text: #fff;
    }
    body {
      margin:0;
      background: var(--bg);
      color: var(--text);
      font-family: 'Segoe UI', sans-serif;
      transition: .3s;
    }
    .title {
      text-align:center;
      margin:40px 0;
      margin-bottom: 6px;
      font-size:2.5rem;
      font-weight:700;
      background:linear-gradient(270deg,#ff00cc,#3333ff,#00ffee,#ff00cc);
      background-size:800% 800%;
      -webkit-background-clip:text;
      -webkit-text-fill-color:transparent;
      animation:move 8s ease infinite;
    }
    @keyframes move {0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
    .container{max-width:1400px;margin:auto;padding:20px;}
    .controls{display:flex;justify-content:center;flex-wrap:wrap;gap:10px;margin-bottom:20px;}
    select,input{padding:10px;border-radius:8px;border:1px solid #444;background:var(--card);color:var(--text);font-size:1rem;transition:.3s;}
    .channel-list{display:grid;grid-template-columns:repeat(2,1fr);gap:18px;}
    .channel{background:var(--card);border:1px solid #333;padding:12px;border-radius:14px;text-align:center;transition:.3s;cursor:pointer;position:relative;}
    .channel:hover{background:#292929;transform:translateY(-6px) scale(1.03);box-shadow:0 12px 25px rgba(0,0,0,0.6);}
    .channel img{width:80px;height:auto;margin-bottom:10px;border-radius:10px;background:#222;}
    .channel-name{font-size:1rem;font-weight:600;margin-bottom:4px;}
    .channel-group{font-size:0.85rem;color:#aaa;margin-bottom:8px;}
    .watch-btn{margin-top:4px;padding:6px 18px;background:var(--btn);color:#fff;border:none;border-radius:8px;font-weight:bold;cursor:pointer;transition:.2s;text-decoration:none;}
    .watch-btn:hover{background:#0056b3;transform:scale(1.05);}
    #keyTimerBadge {
      position:fixed; top:8px; right:8px;
      background:linear-gradient(135deg,#ff0080,#7928ca);
      color:#fff; padding:6px 14px; border-radius:20px;
      font-size:0.9rem; font-weight:600; display:none; z-index:9999;
    }
    .blink { animation: blinkAnim 1s step-start infinite; }
    @keyframes blinkAnim { 50% { opacity: 0; } }
  </style>
</head>
<body>
  <div class="title">Select Category</div>
  <div class="container">

    <!-- Replace the existing gs definition and the Submit button with this -->
    <div class="controls">
      <select id="groupSelect" onchange="filterChannels()">
        <?php foreach ($groups as $g): ?>
          <option value="<?=htmlspecialchars($g)?>"><?=htmlspecialchars($g)?></option>
        <?php endforeach; ?>
      </select>

      <a href="#" id="submitLink" class="submit-theme-btn" title="Submit">
        <i class="fas fa-download"></i> Submit
      </a>

      <a href="#" id="group-selection" class="submit-theme-btn" title="Select Group">
        <i class="fas fa-download"></i> Select Group
      </a>
    </div>
    
  </div>

  <script>
    // Add this to your existing JavaScript
    document.addEventListener('DOMContentLoaded', function() {

      const submitLink = document.getElementById('submitLink');
      const groupSelection = document.getElementById('group-selection');
      const groupSelect = document.getElementById('groupSelect');
      
      function updateSubmitLink() {
        const selectedGroup = groupSelect.value;
        submitLink.href = 'indexweb.php?group=' + encodeURIComponent(selectedGroup) + '&dl=1';
        groupSelection.href = 'genre_checklist.php' + '?dl=1';
      }
      
      // Update on page load
      updateSubmitLink();
      
      // Update when dropdown changes
      groupSelect.addEventListener('change', function() {
        updateSubmitLink();
        filterChannels(); // Keep your existing filter function
      });
    });

    const themeSelect = document.getElementById('themeSelect');

    const stored = localStorage.getItem('sf_theme');
    if (stored) themeSelect.value = stored;
    changeTheme();
    themeSelect.addEventListener('change', () => {
      localStorage.setItem('sf_theme', themeSelect.value);
      changeTheme();
    });
    
    function changeTheme(){
      const t = themeSelect.value;
      const root = document.documentElement.style;
      const themes = {
        default:['#0e0e0e','#1a1a1a','#007bff','#ffffff'],
        yellow:['#222','#333','#ffcc00','#fff'],
        pink:['#2a002a','#3a003a','#ff66b2','#fff'],
        amoled:['#000','#121212','#ff9900','#fff'],
        blue:['#001f3f','#003366','#007bff','#fff'],
        sunset:['#1b0c12','#2a0f19','#ff4d4d','#ffecec'],
        aqua:['#001f23','#002e34','#00ffff','#e0ffff'],
        purple:['#1b1128','#2e1a40','#c77dff','#f2e6ff'],
        mint:['#0f1f1c','#1c2f2b','#80ffcc','#eafff5'],
        cyber:['#01010c','#111122','#ff00ff','#e0e0ff']
      };
      const [bg,card,btn,text] = themes[t] || themes.default;
      root.setProperty('--bg',bg);
      root.setProperty('--card',card);
      root.setProperty('--btn',btn);
      root.setProperty('--text',text);
    }

    function filterChannels(){
      const grp = document.getElementById('groupSelect').value;
      const s = document.getElementById('searchBox').value.toLowerCase();
      document.querySelectorAll('.channel').forEach(ch=>{
        const matches = (grp==='all'|| ch.dataset.group===grp.toLowerCase())
          && ch.dataset.name.includes(s);
        ch.style.display = matches?'block':'none';
      });
    }

    update();
  </script>
</body>
</html>