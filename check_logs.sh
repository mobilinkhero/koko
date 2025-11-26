#!/bin/bash

echo "=== Checking E-commerce Logs ==="
echo ""

# Check home directory
echo "Home directory: $HOME"
echo ""

# Check if log file exists in home
if [ -f "$HOME/ecomorcelog.log" ]; then
    echo "✓ Log file found at: $HOME/ecomorcelog.log"
    echo "Last 20 lines:"
    tail -20 "$HOME/ecomorcelog.log"
else
    echo "✗ Log file not found at: $HOME/ecomorcelog.log"
fi

echo ""
echo "=== Checking Laravel Logs ==="
# Check Laravel logs
if [ -f "storage/logs/laravel.log" ]; then
    echo "✓ Laravel log found"
    echo "Last sync-related entries:"
    grep -i "sync\|sheets\|product" storage/logs/laravel.log | tail -10
else
    echo "✗ Laravel log not found"
fi

echo ""
echo "=== Creating log file if missing ==="
touch "$HOME/ecomorcelog.log"
chmod 666 "$HOME/ecomorcelog.log"
echo "✓ Log file created/verified at: $HOME/ecomorcelog.log"
