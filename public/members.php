<?php
require("../includes/header.php");

if(!isset($_SESSION['loggedin'])) {
    header('Location:http://' . $_SERVER['HTTP_HOST'] . '/'); exit;
} else {
    ?>

    <div class="small-12 columns">
        <h2>Member's Area</h2>
        <h3>Welcome to the Member's Only Area, <?php echo $_SESSION['name']; ?>!</h3>
        <p></p>
    </div>

<?php }

require ("../includes/footer.php");