<?php

@include 'config.php';

session_start();

$admin_id = $_SESSION['admin_id'];

if(!isset($admin_id)){
   header('location:login.php');
   exit; // Add exit to stop further execution
}

$message = []; // Initialize message array to store validation messages

if(isset($_POST['update_product'])){
   // Sanitize and validate product ID
   $pid = filter_input(INPUT_POST, 'pid', FILTER_VALIDATE_INT);
   if($pid === false || $pid <= 0) {
      $message[] = 'Invalid product ID!';
   }

   // Sanitize and validate product name
   $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
   if(empty($name)){
      $message[] = 'Product name is required!';
   } elseif (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
      $message[] = 'Product name should contain only letters and whitespaces!';
   }

   // Sanitize and validate product price
   $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
   if($price === false || $price <= 0) {
      $message[] = 'Invalid product price!';
   }

   // Sanitize and validate product category
   $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
   if(empty($category)){
      $message[] = 'Product category is required!';
   }

   // Sanitize and validate product details
   $details = filter_input(INPUT_POST, 'details', FILTER_SANITIZE_STRING);
   if(empty($details)){
      $message[] = 'Product details are required!';
   }

   if(empty($message)){ // If no validation errors, proceed with updating the product
      $image = $_FILES['image']['name'];
      $image_tmp_name = $_FILES['image']['tmp_name'];
      $image_folder = 'uploaded_img/'.$image;
      $image_size = $_FILES['image']['size'];
      $old_image = $_POST['old_image'];

      // Update product details in the database
      $update_product = $conn->prepare("UPDATE `products` SET name = ?, category = ?, details = ?, price = ? WHERE id = ?");
      $update_product->execute([$name, $category, $details, $price, $pid]);

      $message[] = 'Product updated successfully!';

      if(!empty($image)){
         if($image_size > 2000000){
            $message[] = 'Image size is too large!';
         } else {
            // Update product image in the database
            $update_image = $conn->prepare("UPDATE `products` SET image = ? WHERE id = ?");
            $update_image->execute([$image, $pid]);

            if($update_image){
               move_uploaded_file($image_tmp_name, $image_folder);
               unlink('uploaded_img/'.$old_image);
               $message[] = 'Image updated successfully!';
            }
         }
      }
   }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Products</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
   <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>
   
<?php include 'admin_header.php'; ?>

<section class="update-product">
   <h1 class="title">Update Product</h1>   

   <?php
      $update_id = $_GET['update'];
      $select_products = $conn->prepare("SELECT * FROM `products` WHERE id = ?");
      $select_products->execute([$update_id]);
      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
   ?>
   <form action="" method="post" enctype="multipart/form-data">
      <input type="hidden" name="old_image" value="<?= $fetch_products['image']; ?>">
      <input type="hidden" name="pid" value="<?= $fetch_products['id']; ?>">
      <img src="uploaded_img/<?= $fetch_products['image']; ?>" alt="">
      <input type="text" name="name" placeholder="Enter product name" required class="box" value="<?= $fetch_products['name']; ?>">
      <input type="number" min="0" name="price" placeholder="Enter product price" required class="box" value="<?= $fetch_products['price']; ?>">
      <select name="category" class="box" required>
         <option selected><?= $fetch_products['category']; ?></option>
         <option value="vegitables">Vegitables</option>
         <option value="fruits">Fruits</option>
         <option value="meat">Meat</option>
         <option value="fish">Fish</option>
      </select>
      <textarea name="details" required placeholder="Enter product details" class="box" cols="30" rows="10"><?= $fetch_products['details']; ?></textarea>
      <input type="file" name="image" class="box" accept="image/jpg, image/jpeg, image/png">
      <div class="flex-btn">
         <input type="submit" class="btn" value="Update Product" name="update_product">
         <a href="admin_products.php" class="option-btn">Go Back</a>
      </div>
   </form>
   <?php
         }
      }else{
         echo '<p class="empty">No products found!</p>';
      }
   ?>

</section>

<script src="js/script.js"></script>

</body>
</html>
