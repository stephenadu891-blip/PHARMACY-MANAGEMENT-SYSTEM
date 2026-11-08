<?php
require_once "config/database.php";

echo "PHARMACYCARE AUTOMATED TEST\n";
echo "==========================\n";

$testsPassed = 0;
$testsFailed = 0;

// Test 1: Database connection
if ($conn instanceof mysqli && !$conn->connect_error) {
    echo "PASS: Database connection successful\n";
    $testsPassed++;
} else {
    echo "FAIL: Database connection failed\n";
    $testsFailed++;
}

// Test 2: Required database tables
$tables = [
    "users",
    "medicines",
    "customers",
    "prescriptions",
    "sales",
    "sale_items",
    "paystack_payments"
];

foreach ($tables as $table) {
    $sql = "SHOW TABLES LIKE '$table'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        echo "PASS: $table table exists\n";
        $testsPassed++;
    } else {
        echo "FAIL: $table table missing\n";
        $testsFailed++;
    }
}

// Test 3: Medicine table structure
$result = $conn->query("SHOW COLUMNS FROM medicines");

$columns = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row["Field"];
    }
}

$requiredColumns = [
    "id",
    "medicine_name",
    "quantity",
    "selling_price",
    "expiry_date"
];

if (count(array_diff($requiredColumns, $columns)) === 0) {
    echo "PASS: Medicine table structure is correct\n";
    $testsPassed++;
} else {
    echo "FAIL: Medicine table structure is incomplete\n";
    $testsFailed++;
}

// Summary
echo "\n==========================\n";
echo "Tests passed: $testsPassed\n";
echo "Tests failed: $testsFailed\n";

$conn->close();

if ($testsFailed > 0) {
    exit(1);
}

echo "AUTOMATED TEST COMPLETED\n";
?>