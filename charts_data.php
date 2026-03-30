<?php
require 'db.php';

header('Content-Type: application/json');

// Detect orders schema
$cols = [];
$colResult = $conn->query("SHOW COLUMNS FROM orders");
if($colResult){
    while($row = $colResult->fetch_assoc()){
        $cols[] = $row['Field'];
    }
}

$hasCreatedAt = in_array('created_at', $cols);
$hasQty = in_array('quantity', $cols);
$hasProdName = in_array('product_name', $cols);
$hasName = in_array('name', $cols);
$hasTotal = in_array('total', $cols);
$hasCustomerName = in_array('customer_name', $cols);

// Line chart: orders per day (last 7 days)
$line = [
    "labels" => [],
    "values" => []
];
if($hasCreatedAt){
    $res = $conn->query("SELECT DATE(created_at) as day, COUNT(*) as cnt FROM orders GROUP BY day ORDER BY day DESC LIMIT 7");
    $rows = [];
    if($res){
        while($r = $res->fetch_assoc()){
            $rows[] = $r;
        }
    }
    $rows = array_reverse($rows);
    foreach($rows as $r){
        $line["labels"][] = $r["day"];
        $line["values"][] = (int)$r["cnt"];
    }
}

// Pie chart: product mix by quantity or customer mix by total
$pie = [
    "labels" => [],
    "values" => [],
    "label" => ""
];
if(($hasProdName || $hasName) && $hasQty){
    $nameCol = $hasProdName ? 'product_name' : 'name';
    $res = $conn->query("SELECT $nameCol as label, SUM(quantity) as qty FROM orders GROUP BY $nameCol ORDER BY qty DESC LIMIT 5");
    if($res){
        while($r = $res->fetch_assoc()){
            $pie["labels"][] = $r["label"];
            $pie["values"][] = (int)$r["qty"];
        }
    }
    $pie["label"] = "Top Products (Qty)";
} elseif($hasCustomerName && $hasTotal){
    $res = $conn->query("SELECT customer_name as label, SUM(total) as total FROM orders GROUP BY customer_name ORDER BY total DESC LIMIT 5");
    if($res){
        while($r = $res->fetch_assoc()){
            $pie["labels"][] = $r["label"];
            $pie["values"][] = (float)$r["total"];
        }
    }
    $pie["label"] = "Top Customers (KES)";
}

echo json_encode([
    "line" => $line,
    "pie" => $pie,
    "generated_at" => date('Y-m-d H:i:s')
]);
