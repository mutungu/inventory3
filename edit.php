<?php
session_start();
require 'db.php';

$id = $_GET['id'];

if(isset($_POST['update'])){
    $name=$_POST['name'];
    $price=$_POST['price'];
    $category=$_POST['category'];

    $stmt = $conn->prepare("UPDATE products SET name=?, price=?, category=? WHERE id=?");
    $stmt->bind_param("sdsi",$name,$price,$category,$id);
    $stmt->execute();

    header("Location: admin.php");
}

$result = $conn->query("SELECT * FROM products WHERE id=$id");
$row = $result->fetch_assoc();
?>

<h2>Edit Product</h2>

<form method="POST">
<input type="text" name="name" value="<?= $row['name'] ?>">
<input type="number" name="price" value="<?= $row['price'] ?>">
<input type="text" name="category" value="<?= $row['category'] ?>">
<button name="update">Update</button>
</form>