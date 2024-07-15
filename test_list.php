<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
};

if(isset($_POST['add_product'])){

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $price = $_POST['price'];
   $contact = mysqli_real_escape_string($conn, $_POST['contact']);
   $image = $_FILES['image']['name'];
   $image_size = $_FILES['image']['size'];
   $image_tmp_name = $_FILES['image']['tmp_name'];
   $image_folder = 'uploaded_img/'.$image;

   
      $add_product_query = mysqli_query($conn, "INSERT INTO `products`(name, price, image, contact,seller) VALUES('$name', '$price', '$image', '$contact', '$user_id')") or die('query failed');

      if($add_product_query){
         if($image_size > 2000000){
            $message[] = 'image size is too large';
         }else{
            move_uploaded_file($image_tmp_name, $image_folder);
            $message[] = 'product added successfully!';
         }
      }else{
         $message[] = 'product could not be added!';
      }
   
}

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   $delete_image_query = mysqli_query($conn, "SELECT image FROM `products` WHERE id = '$delete_id'") or die('query failed');
   $fetch_delete_image = mysqli_fetch_assoc($delete_image_query);
   unlink('uploaded_img/'.$fetch_delete_image['image']);
   mysqli_query($conn, "DELETE FROM `products` WHERE id = '$delete_id'") or die('query failed');
   header('location:list_products.php');
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Sell Books</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom admin css file link  -->
   <link rel="stylesheet" href="css/fakeadmin_style.css">
     <script src="sweetalert2.all.min.js"></script>
    <style>.swal2-popup {
        font-size: calc(1vmin + 0.5vmax);
    }</style>
</head>
<body>
   
<?php include 'header.php'; ?>

<!-- product CRUD section starts  -->

<section class="add-products">

   <h1 class="title">my Products</h1>
    <center><input style="margin-bottom:6px;" readonly onclick="pricecheck()" value="Check Book Average Price" class="btn"></center>
   <form method="post" enctype="multipart/form-data">
      <h3>add product</h3>
      
      <input type="text" name="name" class="box" placeholder="enter product name - ISBN # - Grade #" required>
      <!--<h2>Example: Hamlet - ISBN 1451669410 - Grade 9</h2>-->
      <!--<h2>Alternate Example: Flute</h2>-->
      <input type="number" min="0" name="price" class="box" placeholder="enter product price" required>
      <input type="text" name="contact" class="box" placeholder="Enter contact info (email/number)" required>
      <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box" required>
      <input type="submit" value="add product" name="add_product" class="btn">
   </form>

</section>

<!-- product CRUD section ends -->

<!-- show products  -->

<section class="show-products">

   <div class="box-container">

      <?php
         $select_products = mysqli_query($conn, "SELECT * FROM `products` WHERE seller = ".$user_id) or die('query failed');
         if(mysqli_num_rows($select_products) > 0){
            while($fetch_products = mysqli_fetch_assoc($select_products)){
      ?>
      <div class="box">
         <img src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="">
         <div class="name"><?php echo $fetch_products['name']; ?></div>
         <div class="price">$<?php echo $fetch_products['price']; ?></div>
         <a href="list_products.php?delete=<?php echo $fetch_products['id']; ?>" class="delete-btn" onclick="return showAlert(event)">delete</a>
      </div>
      <?php
         }
      }else{
         echo '<p class="empty">no products added yet!</p>';
      }
      ?>
   </div>

</section>








<!-- custom admin js file link  -->
<script src="js/script.js"></script>
<script>
    function pricecheck() {
        Swal.fire({
            title: "Input Your ISBN #",
            text: "Get average market prices for your book. ",
            icon: "warning",
            input: 'text',
            inputPlaceholder: "ISBN #"
        }).then((result) => {
            
            const proxyUrl = "https://corsproxy.io/?";
const targetUrl = "https://booksrun.com/api/v3/price/buy/" + result.value + "?key=cleybpe40lvcud1wgbna";

fetch(proxyUrl + targetUrl)
  .then((res) => res.json())
  .then((data) => {
    let sum = 0;
let count = 0;

function extractValues(obj) {
  for (const key in obj) {
    if (typeof obj[key] === 'object' && obj[key] !== null) {
      extractValues(obj[key]);
    } else if (typeof obj[key] === 'number') {
      sum += obj[key];
      count++;
    }
  }
}

extractValues(data);

var average = (count === 0 ? 0 : sum / count).toFixed(2);
Swal.fire({
    title:"Your Average Price",
    text:"The average market price for your product is: $"+average,
    icon:"success",
    confirmButtonText: "Copy Price",
    showConfirmButton:true,
    showDenyButton:true,
    denyButtonText:"Cancel"
}).then((result) => {
    if (result.isConfirmed) {
        navigator.clipboard.writeText(average);
    }
})
  })
        })
    }

    document.querySelectorAll("form").forEach((el) => {
        el.onsubmit = (e) => {
        if (document.querySelectorAll(".swal2-popup").length==0) {
        e.preventDefault();
        Swal.fire({
            title: "Successfully Listed the Book "+el.childNodes[3].value+" for Sale. It is Being Sold for $"+el.childNodes[9].value+". ",
            text: "You can check the shop for your product. ",
            icon: "success",
        }).then(() => {
            el.childNodes[15].click();
        })
        }
    }
    });
    function showAlert(event) {
        Swal.fire({
          title: 'Are you sure?',
          text: "The book listing of "+event.target.parentElement.childNodes[3].textContent+" for sale (Price: "+event.target.parentElement.childNodes[5].textContent+") will be deleted!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire(
              'Deleted!',
              'The book you listed: '+event.target.parentElement.childNodes[3].textContent+' has been deleted.',
              'success'
            ).then(() => {
                window.location.href = event.target.href;
            })
          }
        })
        return false;
    }
</script>
</body>
</html>