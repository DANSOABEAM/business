<?php
session_start(); // Start the session to access the cart

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "business";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if there is anything in the cart
$cart_items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Fetch products in the cart
$product_details = [];
if (!empty($cart_items)) {
    $placeholders = implode(',', array_fill(0, count($cart_items), '?'));
    $stmt = $conn->prepare("SELECT product_id, name, price, image FROM Products WHERE product_id IN ($placeholders)");
    $stmt->bind_param(str_repeat('i', count($cart_items)), ...$cart_items);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $product_details[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .cart-container {
            margin: 50px auto;
            max-width: 900px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        .cart-item img {
            width: 100px;
            height: auto;
            border-radius: 5px;
        }
        .cart-item h5 {
            margin: 0;
        }
        .remove-btn {
            color: red;
            cursor: pointer;
        }
        .total-price {
            font-size: 24px;
            font-weight: bold;
        }
        .btn-animate {
            position: relative;
            overflow: hidden;
        }
        .btn-animate::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.2);
            transition: all 0.4s;
        }
        .btn-animate:hover::before {
            left: 100%;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="home.php">ShopLogo</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="home.php">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="profile.php">Profile</a>
            </li>
            <li class="nav-item active">
                <a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
        </ul>
    </div>
</nav>

<!-- Cart Page Content -->
<div class="container cart-container">
    <h2 class="text-center mb-4">Your Cart</h2>

    <?php if (!empty($product_details)): ?>
        <?php $total = 0; ?>
        <?php foreach ($product_details as $product): ?>
            <div class="cart-item">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <h5><?php echo htmlspecialchars($product['name']); ?></h5>
                <p>Price: GHS <?php echo htmlspecialchars($product['price']); ?></p>
                <a href="remove_from_cart.php?product_id=<?php echo $product['product_id']; ?>" class="remove-btn"><i class="fas fa-trash-alt"></i> Remove</a>
            </div>
            <?php $total += $product['price']; ?>
        <?php endforeach; ?>

        <div class="total-price text-right mt-4">
            Total: GHS <?php echo number_format($total, 2); ?>
        </div>

        <div class="text-center mt-4">
            <button class="btn btn-success btn-lg btn-animate">Proceed to Checkout</button>
        </div>
    <?php else: ?>
        <p class="text-center">Your cart is empty. <a href="home.php">Continue shopping</a>.</p>
    <?php endif; ?>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
