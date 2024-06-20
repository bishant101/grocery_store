<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit; // Add exit to stop further execution
}

$message = []; // Initialize message array to store validation messages

if(isset($_POST['add_product'])){
   // Sanitize and validate product name
   $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
   if(empty($name)){
      $message[] = 'Product name is required!';
   } elseif (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
      $message[] = 'Product name should contain only letters and whitespaces!';
   }

   // Additional validation to ensure that the name contains at least one letter
   if (!preg_match("/[a-zA-Z]/", $name)) {
      $message[] = 'Product name should contain at least one letter!';
   }

   // Sanitize and validate other input fields
   $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
   $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
   $details = filter_input(INPUT_POST, 'details', FILTER_SANITIZE_STRING);

   if(empty($message)){ // If no validation errors, proceed with adding the product
      $image = $_FILES['image']['name'];
      $image_tmp_name = $_FILES['image']['tmp_name'];
      $image_folder = 'uploaded_img/'.$image;
      $image_size = $_FILES['image']['size'];

      // Check if image file is uploaded
      if(empty($image)){
         $message[] = 'Product image is required!';
      } elseif($image_size > 2000000){ // Check image size
         $message[] = 'Image size is too large!';
      } else {
         // Insert product into database
         $insert_products = $conn->prepare("INSERT INTO `products`(name, category, details, price, image) VALUES(?,?,?,?,?)");
         $insert_products->execute([$name, $category, $details, $price, $image]);

         if($insert_products){
            move_uploaded_file($image_tmp_name, $image_folder);
            $message[] = 'New product added!';
         } else {
            $message[] = 'Failed to add product. Please try again later.';
         }
      }
   }
}

if(isset($_GET['delete'])){
   // Handle product deletion
   $delete_id = $_GET['delete'];

   $select_delete_image = $conn->prepare("SELECT image FROM `products` WHERE id = ?");
   $select_delete_image->execute([$delete_id]);
   $fetch_delete_image = $select_delete_image->fetch(PDO::FETCH_ASSOC);

   // Delete product image file
   if($fetch_delete_image){
      unlink('uploaded_img/'.$fetch_delete_image['image']);
   }

   // Delete product from database
   $delete_products = $conn->prepare("DELETE FROM `products` WHERE id = ?");
   $delete_products->execute([$delete_id]);

   // Delete related entries from wishlist and cart tables
   $delete_wishlist = $conn->prepare("DELETE FROM `wishlist` WHERE pid = ?");
   $delete_wishlist->execute([$delete_id]);
   $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE pid = ?");
   $delete_cart->execute([$delete_id]);

   header('location:admin_products.php');
   exit; // Add exit to stop further execution
}

// Retrieve and display existing products
$show_products = $conn->prepare("SELECT * FROM `products`");
$show_products->execute();
$products = $show_products->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Products</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="add-products">
   <h1 class="title">Add New Product</h1>
   <!-- <?php if(!empty($message)): ?>
      <div class="message">
         <?php foreach($message as $msg): ?>
            <p><?php echo $msg; ?></p>
         <?php endforeach; ?>
      </div>
   <?php endif; ?> -->
   <form action="" method="POST" enctype="multipart/form-data">
      <div class="flex">
         <div class="inputBox">
            <input type="text" name="name" class="box" required placeholder="Enter product name">
            <select name="category" class="box" required>
               <option value="" selected disabled>Select category</option>
               <option value="vegitables">Vegitables</option>
               <option value="fruits">Fruits</option>
               <option value="meat">Meat</option>
               <option value="fish">Fish</option>
            </select>
         </div>
         <div class="inputBox">
            <input type="number" min="0" name="price" class="box" required placeholder="Enter product price">
            <input type="file" name="image" required class="box" accept="image/jpg, image/jpeg, image/png">
         </div>
      </div>
      <textarea name="details" class="box" required placeholder="Enter product details" cols="30" rows="10"></textarea>
      <input type="submit" class="btn" value="Add Product" name="add_product">
   </form>
</section>

<section class="show-products">
   <h1 class="title">Products Added</h1>
   <div class="box-container">
      <?php if(is_array($products) && !empty($products)): ?>
         <?php foreach($products as $product): ?>
            <div class="box">
               <div class="price">$<?= $product['price']; ?>/-</div>
               <img src="uploaded_img/<?= $product['image']; ?>" alt="">
               <div class="name"><?= $product['name']; ?></div>
               <div class="cat"><?= $product['category']; ?></div>
               <div class="details"><?= $product['details']; ?></div>
               <div class="flex-btn">
                  <a href="admin_update_product.php?update=<?= $product['id']; ?>" class="option-btn">Update</a>
                  <a href="admin_products.php?delete=<?= $product['id']; ?>" class="delete-btn" onclick="return confirm('Delete this product?');">Delete</a>
               </div>
            </div>
         <?php endforeach; ?>
      <?php else: ?>
         <p class="empty">No products added yet!</p>
      <?php endif; ?>
   </div>
</section>


<script src="js/script.js"></script>

</body>
</html>
