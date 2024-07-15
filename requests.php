<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
};

if(isset($_GET['delete'])){
   $delete_id = $_GET['delete'];
   mysqli_query($conn, "DELETE FROM `message` WHERE id = '$delete_id'") or die('query failed');
   header('location:requests.php');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Book Requests</title>

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

<section class="messages">

   <h1 class="title">Books Requested by Others</h1>

   <div class="box-container">
   <?php
      $select_message = mysqli_query($conn, "SELECT * FROM `message`") or die('query failed');
      if(mysqli_num_rows($select_message) > 0){
         while($fetch_message = mysqli_fetch_assoc($select_message)){
      
   ?>
   <div class="box">
      <p> book name : <span><?php echo $fetch_message['name']; ?></span> </p>
      <p> contact info : <span><?php echo $fetch_message['email']; ?></span> </p>
      <?php if($fetch_message['poster']==$user_id){echo '<a href="requests.php?delete='.$fetch_message['id'].'" onclick="return showAlert(event)" class="delete-btn">delete book request</a>';}?>
   </div>
   <?php
      };
   }else{
      echo '<p class="empty">there are no requests!</p>';
   }
   ?>
   </div>

</section>
<!-- custom admin js file link  -->
<script src="js/script.js"></script>
<script>
    function showAlert(event) {
        Swal.fire({
          title: 'Are you sure?',
          text: "The request will be deleted!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire(
              'Deleted!',
              'The request you have issued for '+event.target.parentElement.childNodes[1].textContent+' has been deleted.',
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