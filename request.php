<?php

include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if(!isset($user_id)){
   header('location:login.php');
}

if(isset($_POST['send'])){

   $name = mysqli_real_escape_string($conn, $_POST['name']);
   $email = mysqli_real_escape_string($conn, $_POST['email']);
   $msg = mysqli_real_escape_string($conn, $_POST['message']);

   $select_message = mysqli_query($conn, "SELECT * FROM `message` WHERE name = '$name' AND email = '$email'") or die('query failed');

   if(mysqli_num_rows($select_message) > 0){
      $message[] = 'message sent already!';
   }else{
      mysqli_query($conn, "INSERT INTO `message`(user_id, name, email, number, message, poster) VALUES('$user_id', '$name', '$email', 'a', 'a', '$user_id')") or die('query failed');
      $message[] = 'message sent successfully!';
   }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Request a Book</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">
    <script src="sweetalert2.all.min.js"></script>
    <style>.swal2-popup {
        font-size: calc(1vmin + 0.5vmax);
    }</style>
</head>
<body>
   
<?php include 'header.php'; ?>

<div class="heading">
   <h3>Request a book</h3>
   <p> <a href="home.php">home</a> / request </p>
</div>

<section class="contact">

   <form action="" method="post">
      <h3>Request a book!</h3>
      <input type="text" name="name" required placeholder="Book Name" class="box">
      <input type="text" name="email" required placeholder="Contact Info" class="box">
      <input type="submit" value="send message" name="send" class="btn">
   </form>

</section>








<?php include 'footer.php'; ?>

<!-- custom js file link  -->
<script src="js/script.js"></script>
<script>
    document.querySelectorAll("form").forEach((el) => {
        el.onsubmit = (e) => {
        if (document.querySelectorAll(".swal2-popup").length==0) {
        e.preventDefault();
        Swal.fire({
            title: "Successfully Requested the Book "+el.childNodes[3].value+". ",
            text: "You can check all the requested books for your request. ",
            icon: "success",
        }).then(() => {
            el.childNodes[7].click();
        })
        }
    }
    });
</script>
</body>
</html>