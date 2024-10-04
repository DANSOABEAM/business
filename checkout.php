<?php
session_start();

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

// Fetch cart items
$cart_items = [];
$total_price = 0;

if (isset($_SESSION['cart'])) {
    $cart_ids = implode(',', $_SESSION['cart']); 
    $stmt = $conn->prepare("SELECT product_id, name, price, image FROM Products WHERE product_id IN ($cart_ids)");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $total_price += $row['price'];
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
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .checkout-container {
            margin-top: 50px;
        }
        .product-image {
            max-width: 50px;
            height: auto;
            margin-right: 15px;
        }
        .order-summary {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
        }
        .btn-animate {
            transition: transform 0.2s;
        }
        .btn-animate:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body>

<!-- Navbar (reuse from homepage) -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Marketplace</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="home.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i> Cart</a>
                </li>
            </ul>
            <span class="navbar-text">
                Welcome, User
            </span>
        </div>
    </div>
</nav>

<div class="container checkout-container">
    <div class="row">
        <!-- Billing & Shipping Details Form -->
        <div class="col-md-7">
            <h2>Billing & Shipping Details</h2>
            <form method="post" action="confirm_order.php">
                <div class="mb-3">
                    <label for="fullname" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="fullname" name="fullname" required>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Shipping Address</label>
                    <input type="text" class="form-control" id="address" name="address" required>
                </div>
                <div class="mb-3">
                    <label for="city" class="form-label">City</label>
                    <input type="text" class="form-control" id="city" name="city" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone" required>
                </div>

                <h4>Payment Method</h4>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_method" value="credit_card" id="creditCard" checked>
                    <label class="form-check-label" for="creditCard">Credit Card</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_method" value="paypal" id="paypal">
                    <label class="form-check-label" for="paypal">PayPal</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_method" value="cash_on_delivery" id="cashOnDelivery">
                    <label class="form-check-label" for="cashOnDelivery">Cash on Delivery</label>
                </div>

                <button type="submit" class="btn btn-success btn-lg btn-animate mt-3">Confirm Purchase</button>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="col-md-5">
            <h2>Order Summary</h2>
            <div class="order-summary">
                <?php foreach ($cart_items as $item): ?>
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?php echo htmlspecialchars($item['image']); ?>" class="product-image" alt="Product Image">
                        <div>
                            <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                            <p>Price: GHS <?php echo htmlspecialchars($item['price']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                <hr>
                <h4>Total: GHS <?php echo number_format($total_price, 2); ?></h4>
            </div>
        </div>
    </div>
</div>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
