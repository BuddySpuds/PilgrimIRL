#!/bin/bash

# Install deployment tools for macOS

echo "🔧 Installing deployment tools..."
echo ""

# Check if Homebrew is installed
if ! command -v brew &> /dev/null; then
    echo "❌ Homebrew not found. Installing..."
    /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
else
    echo "✅ Homebrew installed"
fi

# Install sshpass for automated SSH
if ! command -v sshpass &> /dev/null; then
    echo "📦 Installing sshpass (for automated SSH)..."
    brew install hudochenkov/sshpass/sshpass
else
    echo "✅ sshpass installed"
fi

# Install lftp for automated FTP
if ! command -v lftp &> /dev/null; then
    echo "📦 Installing lftp (for automated FTP)..."
    brew install lftp
else
    echo "✅ lftp installed"
fi

# Install rsync (usually pre-installed, but check)
if ! command -v rsync &> /dev/null; then
    echo "📦 Installing rsync..."
    brew install rsync
else
    echo "✅ rsync installed"
fi

# Check WP-CLI
if ! command -v wp &> /dev/null; then
    echo "📦 Installing WP-CLI..."
    brew install wp-cli
else
    echo "✅ WP-CLI installed"
fi

echo ""
echo "✅ All deployment tools installed!"
echo ""
echo "You can now run: ./auto-deploy.sh"
