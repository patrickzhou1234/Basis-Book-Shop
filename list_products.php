<?php
include 'config.php';

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
}

if (isset($_POST['add_product'])) {
    $item_type = mysqli_real_escape_string($conn, $_POST['item_type']);
    $grade = mysqli_real_escape_string($conn, $_POST['grade']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $isbn = mysqli_real_escape_string($conn, $_POST['isbn']);
    $condition = mysqli_real_escape_string($conn, $_POST['condition']);
    $price = $_POST['price'];
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $image = $_FILES['image']['name'];
    $image_size = $_FILES['image']['size'];
    $image_tmp_name = $_FILES['image']['tmp_name'];
    $image_folder = 'uploaded_img/' . $image;

    if (empty($contact)) {
        $email_query = mysqli_query($conn, "SELECT email FROM users WHERE id = '$user_id'") or die('query failed');
        if (mysqli_num_rows($email_query) > 0) {
            $user_data = mysqli_fetch_assoc($email_query);
            $contact = $user_data['email'];
        }
    }

    // Combine relevant fields into the name
    if ($item_type === 'book') {
        if (strlen($isbn) < 1) {
            $name = "$title - Grade $grade - Condition $condition";
        } else {
            $name = "$title - ISBN $isbn - Grade $grade - Condition $condition";
        }
    } else {
        $name = "$title";
    }

    $add_product_query = mysqli_query($conn, "INSERT INTO `products`(name, price, image, contact, seller) VALUES('$name', '$price', '$image', '$contact', '$user_id')") or die('query failed');

    if ($add_product_query) {
        if ($image_size > 2000000) {
            $message[] = 'image size is too large';
        } else {
            move_uploaded_file($image_tmp_name, $image_folder);
            $message[] = 'product added successfully!';
        }
    } else {
        $message[] = 'product could not be added!';
    }
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $delete_image_query = mysqli_query($conn, "SELECT image FROM `products` WHERE id = '$delete_id'") or die('query failed');
    $fetch_delete_image = mysqli_fetch_assoc($delete_image_query);
    unlink('uploaded_img/' . $fetch_delete_image['image']);
    mysqli_query($conn, "DELETE FROM `products` WHERE id = '$delete_id'") or die('query failed');
    header('location:list_products.php');
}

// Load the JSON file
$json_file = file_get_contents('books.json');
$book_list = json_decode($json_file, true);

// Extract book titles from the JSON
$lowerSchoolBooks = $book_list['Lower School'];
$upperSchoolBooks = $book_list['Upper School'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Items</title>

    <!-- font awesome cdn link  -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- custom admin css file link  -->
    <link rel="stylesheet" href="css/fakeadmin_style.css">
    <script src="sweetalert2.all.min.js"></script>
    <style>
        .swal2-popup {
            font-size: calc(1vmin + 0.5vmax);
        }

        .condition-options {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }

        .condition-options label {
            display: flex;
            align-items: center;
            font-size: 25px;
            cursor: pointer;
        }

        .condition-options input[type="radio"] {
            margin-right: 5px;
        }
    </style>
</head>

<body>

    <?php include 'header.php'; ?>

    <!-- product CRUD section starts  -->
    <section class="add-products">
        <h1 class="title">My Products</h1>
        <center><input style="margin-bottom:6px;" readonly onclick="pricecheck()" value="Check Book Average Price" class="btn"></center>
        <center><input style="margin-bottom:6px;" readonly onclick="autofill()" value="Autofill With ISBN" class="btn"></center>
        <form method="post" enctype="multipart/form-data">
            <h3>Add Product</h3>
            <select name="item_type" class="box" required onchange="toggleFields(this.value)">
                <option value="" disabled selected>Select item type</option>
                <option value="book">Book</option>
                <option value="instrument">Instrument</option>
                <option value="other">Other</option>
            </select>
            <div id="bookFields" style="display: none;">
                <input type="number" min="1" max="12" name="grade" class="box" placeholder="Enter Grade (1-12)">

                <input type="text" name="isbn" class="box" placeholder="Enter ISBN (optional)">
            </div>
            <script>
                const lowerSchoolBooks = <?php echo json_encode($lowerSchoolBooks); ?>;
                const upperSchoolBooks = <?php echo json_encode($upperSchoolBooks); ?>;

                document.querySelector('input[name="grade"]').addEventListener('input', function() {
                    const grade = this.value;
                    const titleInput = document.querySelector('input[name="title"]');
                    const bookTitlesList = document.getElementById('bookTitles');
                    bookTitlesList.innerHTML = ''; // Clear previous options

                    books = [];
                    if (grade >= 1 && grade <= 5) {
                        books = lowerSchoolBooks[grade];
                    } else if (grade >= 6 && grade <= 12) {
                        books = upperSchoolBooks['High School'];
                    }

                    books.forEach(book => {
                        const option = document.createElement('option');
                        option.value = book['Title'];
                        bookTitlesList.appendChild(option);
                    });
                });

                function toggleFields(itemType) {
                    const bookFields = document.getElementById('bookFields');
                    bookFields.style.display = itemType === 'book' ? 'block' : 'none';
                    const bookFields2 = document.getElementById('bookFields2');
                    bookFields2.style.display = itemType === 'book' ? 'block' : 'none';
                }
            </script>
            <div id="commonFields">
                <input type="text" name="title" class="box" placeholder="Product Title" list="bookTitles">
                <div id="bookFields2" style="display: none;"><datalist id="bookTitles"></datalist></div>
                <label style="font-size:30px;" for="condition">Condition:</label>
                <div class="condition-options">
                    <label><input type="radio" name="condition" value="New" required> New</label>
                    <label><input type="radio" name="condition" value="Like New" required> Like New</label>
                    <label><input type="radio" name="condition" value="Good" required> Good</label>
                    <label><input type="radio" name="condition" value="Fair" required> Fair</label>
                </div>
                <input type="number" min="0" name="price" class="box" placeholder="Product Price" required>
                <input type="text" name="contact" class="box" placeholder="Contact Info (email/number) (Default if left blank)">
                <input type="file" name="image" accept="image/jpg, image/jpeg, image/png" class="box" required>
                <input type="submit" value="Add Product" name="add_product" class="btn">
            </div>
        </form>
    </section>
    <!-- product CRUD section ends -->

    <!-- show products  -->
    <section class="show-products">
        <div class="box-container">
            <?php
            $select_products = mysqli_query($conn, "SELECT * FROM `products` WHERE seller = " . $user_id) or die('query failed');
            if (mysqli_num_rows($select_products) > 0) {
                while ($fetch_products = mysqli_fetch_assoc($select_products)) {
            ?>
                    <div class="box">
                        <img src="uploaded_img/<?php echo $fetch_products['image']; ?>" alt="">
                        <div class="name"><?php echo $fetch_products['name']; ?></div>
                        <div class="price">$<?php echo $fetch_products['price']; ?></div>
                        <a href="list_products.php?delete=<?php echo $fetch_products['id']; ?>" class="delete-btn" onclick="return showAlert(event)">Delete</a>
                    </div>
            <?php
                }
            } else {
                echo '<p class="empty">No products added yet!</p>';
            }
            ?>
        </div>
    </section>

    <!-- custom admin js file link  -->
    <script src="js/script.js"></script>
    <script>
        lowerSchoolBooks = <?php echo json_encode($lowerSchoolBooks); ?>;
        upperSchoolBooks = <?php echo json_encode($upperSchoolBooks); ?>;

        document.querySelector('input[name="grade"]').addEventListener('input', function() {
            grade = this.value;
            titleInput = document.querySelector('input[name="title"]');
            bookTitlesList = document.getElementById('bookTitles');
            bookTitlesList.innerHTML = ''; // Clear previous options

            books = [];
            if (grade >= 1 && grade <= 5) {
                books = lowerSchoolBooks[grade];
            } else if (grade >= 6 && grade <= 12) {
                books = upperSchoolBooks['High School'];
            }

            books.forEach(book => {
                const option = document.createElement('option');
                option.value = book['Title'];
                bookTitlesList.appendChild(option);
            });
        });

        function toggleFields(itemType) {
            const bookFields = document.getElementById('bookFields');
            if (itemType === 'book') {
                bookFields.style.display = 'block';
            } else {
                bookFields.style.display = 'none';
            }
        }

        function pricecheck() {
            Swal.fire({
                title: "Input Your ISBN #",
                text: "Get average market prices for your book.",
                icon: "warning",
                input: 'text',
                inputPlaceholder: "ISBN #"
            }).then((result) => {
                const proxyUrl = "https://corsproxy.io/?";
                const targetUrl = "https://booksrun.com/api/v3/price/buy/" + result.value + "?key=cleybpe40lvcud1wgbna";

                fetch(proxyUrl + targetUrl)
                    .then((res) => res.json())
                    .then((data) => {
                        sum = 0;
                        count = 0;

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
                        var average = sum / count;
                        console.log(average.toFixed(2));
                        if (average.toFixed(2) === "0.00") {
                            Swal.fire("Error", "Could not find item price.", "error");
                        } else if (average.toFixed(2) == "NaN") {
                            Swal.fire("Error", "Could not find item.", "error");
                        } else {
                            Swal.fire("Average Price", "The average price is: $" + average.toFixed(2), "success");
                        }
                    }).catch((error) => {
                        Swal.fire("Error", "Could not fetch the price.", "error");
                    });
            });
        }

        function autofill() {
            Swal.fire({
                title: "Input Your ISBN #",
                text: "Get average market prices for your book.",
                icon: "warning",
                input: 'text',
                inputPlaceholder: "ISBN #"
            }).then((result) => {
                const proxyUrl = "https://corsproxy.io/?";
                const targetUrl = "https://booksrun.com/api/v3/price/buy/" + result.value + "?key=cleybpe40lvcud1wgbna";
                const imageUrl = "https://bookcover.longitood.com/bookcover/" + result.value
                const infoUrl = "https://www.googleapis.com/books/v1/volumes?q=isbn:" + result.value;

                fetch(proxyUrl + targetUrl)
                    .then((res) => res.json())
                    .then((data) => {
                        fetch(proxyUrl + imageUrl)
                            .then((res) => res.json())
                            .then((data2) => {
                                fetch(proxyUrl + infoUrl)
                                    .then((res) => res.json())
                                    .then((data3) => {
                                        sum = 0;
                                        count = 0;

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
                                        var average = sum / count;
                                        console.log(average.toFixed(2));
                                        if (average.toFixed(2) === "0.00") {
                                            Swal.fire("Error", "Could not find item price.", "error");
                                        } else if (average.toFixed(2) == "NaN") {
                                            Swal.fire("Error", "Could not find item.", "error");
                                        } else {
                                            title = data3["items"]["0"]["volumeInfo"]["title"] + " by: " + data3["items"]["0"]["volumeInfo"]["authors"]["0"]
                                            Swal.fire({
                                                title: title,
                                                text: "The average price is: $" + average.toFixed(2),
                                                icon: "success",
                                                imageUrl: data2["url"],
                                                imageWidth: 200,
                                                imageHeight: 200,
                                                imageAlt: "Custom image",
                                                showCancelButton: true,
                                                confirmButtonText: "Use these stats to autofill",
                                                cancelButtonText: "No, cancel",
                                                confirmButtonColor: "#dc3545",
                                                cancelButtonColor: "#6c757d",
                                            }).then((result2) => {
                                                if (result2.isConfirmed) {
                                                    document.querySelector('[name="isbn"]').value = result.value
                                                    document.querySelector('[name="title"').value = title
                                                    document.querySelector('[name="price"').value = average.toFixed(2)
                                                    // document.querySelector('[name="item_type"]').value="Book"
                                                    saveImageToLocal(data2["url"])
                                                }
                                            })
                                        }

                                    });

                            }).catch((error) => {
                                Swal.fire("Error", "Could not fetch the price.", "error");
                            });
                    });
            });
        }

        function saveImageToLocal(url) {
            fetch(url)
                .then(response => response.blob())
                .then(blob => {
                    const blobUrl = URL.createObjectURL(blob);

                    const downloadLink = document.createElement('a');
                    downloadLink.href = blobUrl;
                    downloadLink.download = 'image.jpg';
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    document.body.removeChild(downloadLink);

                    URL.revokeObjectURL(blobUrl); // Clean up
                })
                .catch(error => {
                    console.error('Failed to download image:', error);
                });


        }


        function showAlert(event) {
            event.preventDefault();
            const url = event.target.href;
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "No, cancel!"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        }
    </script>
</body>

</html>