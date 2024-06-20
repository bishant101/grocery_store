<?php

if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}

?>

<header class="header" style="background-color: #1C4E80; color: white;">

   <div class="flex">

      <a href="admin_page.php" class="logo" style="color: white;">Admin<span style="color: #EA6A47;">Panel</span></a>

      <nav class="navbar" >
         <a href="admin_page.php" style="color: white;" >Home</a>
         <a href="admin_products.php" style="color: white;">Products</a>
         <a href="admin_orders.php" style="color: white;">Orders</a>
         <a href="admin_users.php" style="color: white;">Users</a>
         <a href="admin_contacts.php" style="color: white;">Messages</a>
      </nav>

      <div class="icons" style="color: white;">
         <div id="menu-btn" class="fas fa-bars"></div>
         <div id="user-btn" class="fas fa-user" style="color: white;"></div>
      </div>

      <div class="profile">
         <?php
            $select_profile = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
            $select_profile->execute([$admin_id]);
            $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
         ?>
         <img src="uploaded_img/<?= $fetch_profile['image']; ?>" alt="">
         <p><?= $fetch_profile['name']; ?></p>
         <a href="admin_update_profile.php" class="btn">update profile</a>
         <a href="logout.php" class="delete-btn">logout</a>
         <div class="flex-btn">
            <a href="login.php" class="option-btn">login</a>
            <a href="register.php" class="option-btn">register</a>
         </div>
      </div>

   </div>
   
</header>
