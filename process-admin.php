<?php
// Start session for admin authentication (optional)
session_start();

// Check if admin is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo "<p>You are not authorized to access this page. Please <a href='admin.html'>log in</a>.</p>";
    exit();
}

// Database connection (replace with your database credentials)
$conn = new mysqli('localhost', 'root', '', 'puneweld');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['productname'];
    $product_desc = $_POST['productdesc'];
    $target_file = "";

    // Handle optional image upload
    if (isset($_FILES['productimage']) && $_FILES['productimage']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['productimage']['name']);
        if (move_uploaded_file($_FILES['productimage']['tmp_name'], $target_file)) {
            echo "<p>Image uploaded successfully.</p>";
        } else {
            echo "<p>Error uploading image.</p>";
            $target_file = ""; // Reset if upload fails
        }
    }

    // Update product details in database
    $query = "UPDATE products SET name=?, description=?";
    $params = [$product_name, $product_desc];

    // Append image path if uploaded
    if ($target_file) {
        $query .= ", image=?";
        $params[] = $target_file;
    }

    $query .= " WHERE id=?";
    $params[] = $product_id;

    $stmt = $conn->prepare($query);
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);

    if ($stmt->execute()) {
        echo "<p>Product updated successfully!</p>";
        header("Location: admin-dashboard.html");
        exit();
    } else {
        echo "<p>Error updating product: " . $conn->error . "</p>";
    }

    $stmt->close();
}
$conn->close();
?>
