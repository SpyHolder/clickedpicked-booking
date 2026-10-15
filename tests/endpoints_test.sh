#!/bin/bash

BASE_URL="http://127.0.0.1:8000"
RESULTS_FILE="tests/endpoint_test_results.txt"

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "=== ENDPOINT TESTING REPORT ===" > $RESULTS_FILE
echo "Timestamp: $(date)" >> $RESULTS_FILE
echo "Base URL: $BASE_URL" >> $RESULTS_FILE
echo "================================" >> $RESULTS_FILE
echo "" >> $RESULTS_FILE

# Function to test endpoint
test_endpoint() {
    local method=$1
    local endpoint=$2
    local description=$3
    local data=$4
    
    echo -n "Testing $method $endpoint... "
    
    if [ "$method" = "GET" ]; then
        response=$(curl -s -w "\n%{http_code}" -X GET "$BASE_URL$endpoint")
    elif [ "$method" = "POST" ]; then
        response=$(curl -s -w "\n%{http_code}" -X POST "$BASE_URL$endpoint" -H "Content-Type: application/json" -d "$data")
    elif [ "$method" = "PUT" ]; then
        response=$(curl -s -w "\n%{http_code}" -X PUT "$BASE_URL$endpoint" -H "Content-Type: application/json" -d "$data")
    elif [ "$method" = "DELETE" ]; then
        response=$(curl -s -w "\n%{http_code}" -X DELETE "$BASE_URL$endpoint")
    fi
    
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')
    
    echo "$method $endpoint" >> $RESULTS_FILE
    echo "Description: $description" >> $RESULTS_FILE
    echo "HTTP Code: $http_code" >> $RESULTS_FILE
    echo "Response (first 200 chars): ${body:0:200}" >> $RESULTS_FILE
    echo "---" >> $RESULTS_FILE
    
    if [ "$http_code" = "200" ] || [ "$http_code" = "201" ]; then
        echo -e "${GREEN}✓ $http_code${NC}"
    elif [ "$http_code" = "404" ]; then
        echo -e "${RED}✗ 404 Not Found${NC}"
    elif [ "$http_code" = "405" ]; then
        echo -e "${YELLOW}! 405 Method Not Allowed${NC}"
    else
        echo -e "${YELLOW}! $http_code${NC}"
    fi
}

echo "PUBLIC ROUTES (Customer Access)"
echo "================================"
test_endpoint "GET" "/rooms" "List all rooms"
test_endpoint "GET" "/rooms/1" "Get room detail"
test_endpoint "GET" "/rooms/1/packages" "Get room packages"
test_endpoint "POST" "/bookings" "Create booking" '{"room_id":1}'
test_endpoint "GET" "/bookings/ABC123" "Get booking by code"
test_endpoint "POST" "/bookings/check-availability" "Check slot availability" '{"room_id":1,"date":"2026-10-20"}'
test_endpoint "POST" "/bookings/ABC123/payments" "Create payment for booking" '{"amount":500000}'
test_endpoint "POST" "/payments/1/upload-proof" "Upload payment proof"

echo ""
echo "ADMIN ROUTES (Protected)"
echo "================================"
test_endpoint "POST" "/admin/rooms" "Create room (Admin)" '{"name":"Studio A"}'
test_endpoint "PUT" "/admin/rooms/1" "Update room (Admin)" '{"name":"Studio B"}'
test_endpoint "DELETE" "/admin/rooms/1" "Delete room (Admin)"
test_endpoint "POST" "/admin/rooms/1/packages" "Create package (Admin)" '{"name":"Package A"}'
test_endpoint "PUT" "/admin/packages/1" "Update package (Admin)" '{"name":"Package B"}'
test_endpoint "DELETE" "/admin/packages/1" "Delete package (Admin)"
test_endpoint "GET" "/admin/bookings" "List bookings (Admin)"
test_endpoint "POST" "/admin/bookings/ABC123/confirm" "Confirm booking (Admin)"
test_endpoint "POST" "/admin/bookings/ABC123/cancel" "Cancel booking (Admin)"
test_endpoint "POST" "/admin/bookings/ABC123/complete" "Complete booking (Admin)"
test_endpoint "POST" "/admin/payments/1/verify" "Verify payment (Admin)" '{"status":"verified"}'
test_endpoint "POST" "/admin/payments/1/reject" "Reject payment (Admin)" '{"status":"rejected"}'

echo ""
echo "Tests completed. Results saved to: $RESULTS_FILE"