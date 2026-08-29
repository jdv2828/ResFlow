#!/bin/bash
# Run Laravel Dusk tests with a temporary dev server for Docker Selenium.
#
# Usage:
#   ./scripts/dusk.sh                       # run all tests
#   ./scripts/dusk.sh tests/Browser/LoginTest.php  # run one file
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
PORT=8000
HOST="0.0.0.0"
DOCKER_HOST="172.17.0.1"
PIDFILE="/tmp/dusk_server.pid"

cleanup() {
    if [ -f "$PIDFILE" ]; then
        kill -9 "$(cat "$PIDFILE")" 2>/dev/null || true
        rm -f "$PIDFILE"
    fi
    lsof -ti:"$PORT" 2>/dev/null | xargs -r kill -9 2>/dev/null || true
}

trap cleanup EXIT

# Kill any stale server.
cleanup

# Start the dev server in the background.
cd "$PROJECT_DIR"
nohup php artisan serve --host="$HOST" --port="$PORT" > /tmp/dusk_server.log 2>&1 &
SERVER_PID=$!
echo "$SERVER_PID" > "$PIDFILE"

# Wait for server to be ready.
for i in $(seq 1 30); do
    if curl -s "http://$DOCKER_HOST:$PORT/" > /dev/null 2>&1; then
        break
    fi
    sleep 0.5
done

echo "Server running at http://$DOCKER_HOST:$PORT (PID $SERVER_PID)"

# Run the tests.
if [ $# -gt 0 ]; then
    php artisan dusk "$@"
else
    php artisan dusk
fi
