<?php
session_start();

// Check if user is logged in and is an Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}
include('database_connection.php');

// Initialize message variables
$successMessage = '';
$errorMessage = '';

// Handle form submission to add a category
if (isset($_POST['add_category'])) {
    $category_name = $_POST['category_name'];

    if (!empty($category_name)) {
        $sql = "INSERT INTO categories (category_name) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $category_name);
        if ($stmt->execute()) {
            $successMessage = "Category added successfully!";
        } else {
            $errorMessage = "Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        $errorMessage = "Category name cannot be empty.";
    }
}

// Handle form submission to upload an image
if (isset($_POST['upload_image'])) {
    $category_id = $_POST['category_id'];
    $image = $_FILES['image']['name'];
    $target_dir = "Uploads/";
    $target_file = $target_dir . basename($image);

    // Move uploaded file to the target directory
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $sql = "INSERT INTO gallery_images (image_url, category_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $target_file, $category_id);
        if ($stmt->execute()) {
            $successMessage = "Image uploaded successfully!";
        } else {
            $errorMessage = "Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        $errorMessage = "Error uploading the image.";
    }
}

// Fetch categories for the dropdown
$categories = [];
$sql = "SELECT * FROM categories";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Handle category deletion
if (isset($_POST['delete_category'])) {
    $category_id = $_POST['category_id'];
    $sql = "DELETE FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    if ($stmt->execute()) {
        $successMessage = "Category deleted successfully!";
    } else {
        $errorMessage = "Error deleting category: " . $conn->error;
    }
    $stmt->close();
}

// Handle image deletion
if (isset($_POST['delete_image'])) {
    $image_id = $_POST['image_id'];

    // Fetch image path to delete the file from the server
    $image_query = "SELECT image_url FROM gallery_images WHERE id = ?";
    $stmt = $conn->prepare($image_query);
    $stmt->bind_param("i", $image_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $image_path = $row['image_url'];

        // Delete the image file
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    $stmt->close();

    // Delete the image record from the database
    $sql = "DELETE FROM gallery_images WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $image_id);
    if ($stmt->execute()) {
        $successMessage = "Image deleted successfully!";
    } else {
        $errorMessage = "Error deleting image: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        /* Animations */
        @keyframes bounceIn {
            0% { opacity: 0; transform: scale(0.8) rotate(-2deg); }
            60% { opacity: 1; transform: scale(1.05) rotate(1deg); }
            100% { transform: scale(1) rotate(0deg); }
        }
        .animate-bounceIn { animation: bounceIn 0.6s ease-out forwards; }
        @keyframes glow {
            from { box-shadow: 0 0 5px rgba(234, 179, 8, 0.5); }
            to { box-shadow: 0 0 15px rgba(234, 179, 8, 0.8); }
        }
        input:focus, select:focus { animation: glow 1s ease-in-out infinite alternate; }
        /* Label animation */
        .form-group label {
            transition: all 0.3s ease;
            background: white;
            padding: 0 4px;
            line-height: 1;
        }
        .form-group input:focus + label,
        .form-group input:not(:placeholder-shown) + label,
        .form-group select:focus + label,
        .form-group select:not(:placeholder-shown) + label {
            transform: translateY(-2.2rem) scale(0.9);
            color: #eab308;
        }
        /* Shake effect for invalid input */
        .shake { animation: shake 0.3s ease-in-out; }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
        /* Button gradient slide effect */
        .btn-gradient {
            position: relative;
            overflow: hidden;
        }
        .btn-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }
        .btn-gradient:hover::before { left: 100%; }
        /* Pulse animation for button */
        .hover-loop { animation: pulse 2s infinite; }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        /* Image hover effect */
        .gallery-image {
            transition: all 0.3s ease;
        }
        .gallery-image:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        /* Notification animation */
        @keyframes slideDownFadeOut {
            0% { opacity: 0; transform: translateY(-20px); }
            10% { opacity: 1; transform: translateY(0); }
            90% { opacity: 1; transform: translateY(0); }
            100% { opacity: 0; transform: translateY(-20px); }
        }
        .notification {
            animation: slideDownFadeOut 2.5s ease-in-out forwards;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Sidebar -->
    <?php include('navbar.php'); ?>

    <!-- Header -->
    <header class="bg-gradient-to-r from-yellow-900 to-yellow-700 text-white py-4 px-6 shadow-md ml-[68px] flex items-center">
        <div class="flex-1 text-center">
            <h1 class="text-2xl font-bold">Gallery</h1>
        </div>
    </header>

    <!-- Notifications -->
    <?php if ($successMessage): ?>
        <div id="successNotification" class="notification fixed top-4 left-1/2 transform -translate-x-1/2 z-50 bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-md shadow-lg text-sm">
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div id="errorNotification" class="notification fixed top-4 left-1/2 transform -translate-x-1/2 z-50 bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-3 rounded-md shadow-lg text-sm">
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="ml-[68px] p-6 space-y-6">
        <!-- Add Category Section -->
        <section class="bg-white p-6 rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105 animate-bounceIn">
            <h2 class="text-xl font-semibold text-center text-gray-800 mb-4">Add Category</h2>
            <form action="" method="POST" id="addCategoryForm" class="space-y-4">
                <div class="form-group relative">
                    <input type="text" id="category_name" name="category_name" required class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer" placeholder=" ">
                    <label for="category_name" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Category Name</label>
                </div>
                <button type="submit" name="add_category" class="w-full p-3 bg-gradient-to-r from-yellow-900 to-yellow-700 text-white rounded-md btn-gradient hover:bg-yellow-800 transition duration-200 hover-loop">Add Category</button>
            </form>
        </section>

        <!-- Upload Image Section -->
        <section class="bg-white p-6 rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105 animate-bounceIn">
            <h2 class="text-xl font-semibold text-center text-gray-800 mb-4">Upload Image</h2>
            <form action="" method="POST" enctype="multipart/form-data" id="uploadImageForm" class="space-y-4">
                <div class="form-group relative">
                    <select id="category_id" name="category_id" required class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200 peer">
                        <option value="" disabled selected>Select a category</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo $category['category_name']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="category_id" class="absolute left-3 top-3 text-sm font-medium text-gray-700 transition-all duration-200">Select Category</label>
                </div>
                <div class="form-group">
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Choose Image</label>
                    <input type="file" id="image" name="image" accept="image/*" required class="w-full p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-yellow-600 focus:border-yellow-600 transition duration-200">
                </div>
                <button type="submit" name="upload_image" class="w-full p-3 bg-gradient-to-r from-yellow-900 to-yellow-700 text-white rounded-md btn-gradient hover:bg-yellow-800 transition duration-200 hover-loop">Upload Image</button>
            </form>
        </section>

        <!-- Existing Categories Section -->
        <section class="bg-white p-6 rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105 animate-bounceIn">
            <h2 class="text-xl font-semibold text-center text-gray-800 mb-4">Existing Categories</h2>
            <ul class="space-y-2">
                <?php foreach ($categories as $category): ?>
                    <li class="flex justify-between items-center p-2 hover:bg-gray-50 rounded-md transition">
                        <span class="text-gray-700"><?php echo $category['category_name']; ?></span>
                        <form action="" method="POST" class="inline">
                            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                            <button type="submit" name="delete_category" onclick="return confirm('Are you sure you want to delete this category?');" class="p-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition duration-200 btn-gradient">Delete</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <!-- Uploaded Images Section -->
        <section class="bg-white p-6 rounded-lg shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:scale-105 animate-bounceIn">
            <h2 class="text-xl font-semibold text-center text-gray-800 mb-4">Uploaded Images</h2>
            <?php
            $images_query = "SELECT gallery_images.id, gallery_images.image_url, categories.category_name 
                             FROM gallery_images 
                             INNER JOIN categories ON gallery_images.category_id = categories.id";
            $images_result = $conn->query($images_query);
            ?>
            <?php if ($images_result->num_rows > 0): ?>
                <ul class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <?php while ($image = $images_result->fetch_assoc()): ?>
                        <li class="flex flex-col items-center p-2 hover:bg-gray-50 rounded-md transition">
                            <img src="<?php echo $image['image_url']; ?>" alt="Image" class="gallery-image w-24 h-auto border border-gray-300 rounded-md mb-2">
                            <span class="text-gray-700 text-sm"><?php echo $image['category_name']; ?></span>
                            <form action="" method="POST" class="inline">
                                <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                <button type="submit" name="delete_image" onclick="return confirm('Are you sure you want to delete this image?');" class="p-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition duration-200 btn-gradient">Delete</button>
                            </form>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p class="text-gray-600 text-center">No images uploaded yet.</p>
            <?php endif; ?>
        </section>
    </main>

    <script>
        // Add shake effect on invalid form submission
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const inputs = this.querySelectorAll('input, select');
                let valid = true;
                inputs.forEach(input => {
                    if (!input.value) {
                        input.classList.add('shake', 'border-red-500');
                        valid = false;
                        setTimeout(() => input.classList.remove('shake'), 300);
                    }
                });
                if (!valid) e.preventDefault();
            });
        });

        // Remove shake effect after input
        document.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('border-red-500');
            });
        });

        // Auto-hide notifications after 2.5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(notification => {
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 2500);
            });
        });
    </script>

    <?php
    $conn->close();
    ?>
</body>
</html>