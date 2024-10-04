<?php
session_start(); // Start session to access user data

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

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php"); // Redirect to login page
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Fetch user information
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, profile_picture, phone_number FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();
$stmt->close();

// Check if user data exists
$user_name = isset($user_data['name']) ? htmlspecialchars($user_data['name']) : 'User';
$user_profile_picture = isset($user_data['profile_picture']) ? htmlspecialchars($user_data['profile_picture']) : 'default-profile.png'; // Set a default image
$user_phone = isset($user_data['phone']) ? htmlspecialchars($user_data['phone']) : 'N/A'; // Fallback for phone number

// Handle search functionality
$search_query = '';
if (isset($_POST['search'])) {
    $search_query = $_POST['search_query'];
    $product_stmt = $conn->prepare("SELECT product_id, name, price, description, category, image FROM Products WHERE name LIKE ?");
    $search_param = "%" . $search_query . "%";
    $product_stmt->bind_param("s", $search_param);
} else {
    $product_stmt = $conn->prepare("SELECT product_id, name, price, description, category, image FROM Products ORDER BY category");
}
$product_stmt->execute();
$product_result = $product_stmt->get_result();

// Fetch products by categories
$products_by_category = [];
while ($product = $product_result->fetch_assoc()) {
    $category = $product['category'];
    $products_by_category[$category][] = $product; // Group products by category
}

// Handle product submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $product_description = $_POST['product_description'];
    $product_category = $_POST['product_category'];
    $product_image = $_FILES['product_image']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($product_image);

    // Move the uploaded file to the target directory
    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO Products (name, price, description, category, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sdsss", $product_name, $product_price, $product_description, $product_category, $target_file);

        if ($stmt->execute()) {
            echo "<script>alert('Product added successfully!');</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Error uploading file.');</script>";
    }
}

// Handle cart functionality
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $_SESSION['cart'][] = $product_id;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Custom styles if needed */
        .navbar-brand img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .category-title {
            margin-top: 40px;
        }
        .user-info {
            text-align: left;
            margin-left: 10px;
        }
        .user-info div {
            margin: 0;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <a class="navbar-brand" href="#">
            <img src="<?php echo $user_profile_picture; ?>" alt="Profile Picture">
            <div class="user-info">
                <div><?php echo $user_name; ?></div>
                <div><a href="tel:<?php echo $user_phone; ?>"><?php echo $user_phone; ?></a></div>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">

            <!-- Search Bar -->
            <form class="form-inline mx-auto" method="POST">
                <input class="form-control mr-sm-2" type="search" placeholder="Search products..." aria-label="Search" name="search_query" value="<?php echo htmlspecialchars($search_query); ?>">
                <button class="btn btn-outline-light my-2 my-sm-0" type="submit" name="search">Search</button>
            </form>

            <!-- User Info and Cart -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="?logout=1">Logout</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cart.php"><i class="fas fa-shopping-cart"></i></a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Add Product Button -->
    <div class="container mt-4 text-right">
        <button class="btn btn-primary" data-toggle="modal" data-target="#addProductModal">Add Product</button>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProductModalLabel">Add Product</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">

                        <!-- Form Fields -->
                        <div class="form-group">
                            <label for="product_name">Product Name</label>
                            <input type="text" class="form-control" name="product_name" required>
                        </div>
                        <div class="form-group">
                            <label for="product_price">Price (GHS)</label>
                            <input type="number" step="0.01" class="form-control" name="product_price" required>
                        </div>
                        <div class="form-group">
                            <label for="product_description">Description</label>
                            <textarea class="form-control" name="product_description" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="product_category">Category</label>
                            <select class="form-control" name="product_category" required>
                                <option value="electronics">Electronics</option>
                                <option value="food">Food</option>
                                <option value="fashion">Fashion</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="product_image">Upload Image</label>
                            <input type="file" class="form-control-file" name="product_image" accept="image/*" required>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add_product" class="btn btn-primary">Submit</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mt-4">
        <?php if (empty($products_by_category)): ?>
            <h3>No products available.</h3>
        <?php else: ?>
            <?php foreach ($products_by_category as $category => $products): ?>
                <h3 class="category-title"><?php echo htmlspecialchars(ucfirst($category)); ?></h3>
                <div class="row">
                    <?php foreach ($products as $product): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <p class="card-text">Price: GHS <?php echo htmlspecialchars(number_format($product['price'], 2)); ?></p>
                                    <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                                    <form method="post">
                                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                        <button type="submit" name="add_to_cart" class="btn btn-success">Add to Cart</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
</body>
</html>
