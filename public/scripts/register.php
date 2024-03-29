<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . "/../scripts/common.php");

/**
 * Checks to ensure it's a legitimate post request
 * Moves on to process it, or else redirects back to the registration page.
 */
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // this is a legit post and you should go ahead and process this stuff below yo

    // This will be an array that stores our errors for the session
    $errors = [];

    /**
     * Filters and trims input then returns sanitized value
     * @param $input string data from form
     * @return string of sanitized data
     */
    function filterTextInput($input){
        return htmlentities(trim($input), ENT_QUOTES);
    }

    /**
     * Filters and ensures input is numeric only and returns value
     * @param $input string data from form
     * @return mixed numeric value
     */
    function filterNumericInput($input){
        return intval(preg_replace('/\D/', '', $input));
    }

    /**
     * Filters and trims email input and returns sanitized email
     * @param $input string data from form
     * @return mixed either filtered email address
     */
    function filterEmailInput($input){
        return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Hashes the user's password for storing into the database
     * Will return false if it fails, otherwise will return the hashed password
     * @param $password
     * @return bool|string
     */
    function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }


    /**
     * Checks to see if the user already exists in the database
     * @param $email
     * @return bool
     */
    function doesUserExist($db, $email) {
        // prepared statement for running the query
        $query = "SELECT 1 FROM users WHERE email = :email";
        // parameters to pass onto the prepared statement
        // it is done this way to prevent any SQL injection
        $query_params = array(':email' => $email);
        // try prepared statement, handle any exceptions
        try{
            $stmt = $db->prepare($query);
            $stmt->execute($query_params);

            if($stmt->rowCount() > 0){
                return true;
            } else {
                return false;
            }
        } catch(PDOException $exception) {
            error_log($exception->getMessage());
            $errors['general'] = "Sorry, we could not complete your request at this time. Please try again later.";
            die(header('Location:/register'));
        }
    }


    /**
     * Creates a new user or if it fails it will redirect to the register page
     * @param $newUser array of filtered data
     * @return bool
     */
    function createNewUser($db, $newUser){
        /**
         * Creates a query for inserting the new user into the database
         */

        $query = "INSERT INTO users (
            `name`, `email`, `address`, `city`, `state`, `zip`, `dayphone`, `evephone`, `password`
        ) VALUES (
            :name, :email, :address, :city, :state, :zip, :dayphone, :evephone, :password
        )";

        /**
         * executes the query and returns an exception if it fails for some unknown god forsaken reason
         */
        try {
            $stmt = $db->prepare($query);
            $stmt->execute($newUser);

            return true;
        } catch(PDOException $exception){
            error_log($exception->getMessage());
            $errors = "Sorry, there was an error completing your registration. Please try again later.";
            $_SESSION['registration_errors']['general'] = $errors;
            die(header('Location:/register'));
        }
    }



    // Here is where we start processing stuff.

    /**
     * Filters the email address and ensures it doesn't exist already in the DB
     * If it does, it will throw an error and return the user to the form
     */
    if(empty($_POST['email'])){
        $errors['email'] = "Please enter your email address.";
    } else {
        $email = filterEmailInput($_POST['email']);
        $_SESSION['email'] = $email;
        if($email){
            if (doesUserExist($db, $email)) {
                $errors['userexists'] = "A user already exists with that email address.
                Try <a href='/login'>Logging in?</a>";
                $_SESSION['registration_errors'] = $errors;
                die(header('Location:/register'));
            }
        } else {
            $errors['email'] = "Please enter a valid email address.";
        }
    }

    /**
     * Filters the name post item
     */
    if(empty($_POST['name'])){
        $errors['name'] = "Please enter your name.";
    } else {
        $name = filterTextInput($_POST['name']);
        $_SESSION['name'] = $name;
    }

    /**
     * Filters the password post item
     */
    if(empty($_POST['password'])){
        $errors['password'] = "Please enter a password.";
    } else {
        if(strlen($_POST['password']) < 8) {
            $errors['password'] = "Your password must be at least 8 characters.";
        } else {
            $password = hashPassword($_POST['password']);
            if ($password === false) {
                $errors['password'] = "There was a problem with the password you chose. Please try again.";
            }
        }
    }

    /**
     * Filters the address post item
     */
    if(empty($_POST['address'])){
        $errors['address'] = "Please enter your street address.";
    } else {
        $address = filterTextInput($_POST['address']);
        $_SESSION['address'] = $address;
    }

    /**
     * Filters the city post item
     */
    if(empty($_POST['city'])){
        $errors['city'] = "Please enter your city.";
    } else {
        $city = filterTextInput($_POST['city']);
        $_SESSION['city'] = $city;
    }

    // List of state abbreviations
    $us_state_abbrevs = array('AL', 'AK', 'AS', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'DC', 'FM', 'FL', 'GA', 'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MH', 'MD', 'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 'NM', 'NY', 'NC', 'ND', 'MP', 'OH', 'OK', 'OR', 'PW', 'PA', 'PR', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VT', 'VI', 'VA', 'WA', 'WV', 'WI', 'WY', 'AE', 'AA', 'AP');

    /**
     * Filters the state post item
     */
    if(empty($_POST['state'])){
        $errors['state'] = "Please enter your state.";
    } else {
        if(in_array($_POST['state'], $us_state_abbrevs)) {
            $state = $_POST['state'];
            $_SESSION['state'] = $state;
        } else {
            $errors['state'] = "Please enter a valid state abbreviation.";
            $_SESSION['state'] = $_POST['state'];
        }
    }

    /**
     * Filters the zip post item
     */
    if(empty($_POST['zip'])){
        $errors['zip'] = "Please enter your zip code.";
    } else {
        $zip = filterNumericInput($_POST['zip']);
        $_SESSION['zip'] = $zip;
        if(strlen($zip) !== 5) {
            $errors['zip'] = "Please enter a valid zip code.";
            $_SESSION['zip'] = $_POST['zip'];
        }
    }


    /**
     * Filters the dayphone post item
     */
    if(empty($_POST['dayphone'])) {
        $errors['dayphone'] = "Please enter your daytime phone number.";
    } else {
        $dayphone = filterNumericInput($_POST['dayphone']);
        $_SESSION['dayphone'] = $dayphone;
        if(strlen($dayphone) < '10' ||  strlen($dayphone) >'11') {
            $errors['dayphone'] = "Please enter a valid daytime phone number.";
        }
    }


    /**
     * Filters the evephone post item
     */
    if(empty($_POST['evephone'])) {
        $errors['dayphone'] = "Please enter your evening phone number.";
    } else {
        $evephone = filterNumericInput($_POST['evephone']);
        $_SESSION['evephone'] = $evephone;
        if(strlen($evephone) < '10' || strlen($evephone) >  '11') {
            $errors['evephone'] = "Please enter a valid evening phone number.";
        }
    }

    /**
     * This checks for registration errors
     * If any are found, it will redirect to the registration form
     * If none are found, it will process the registration.
     */
    if(empty($errors)) {

        /**
         * Creates a new user array using the input submitted
         * this will then be passed onto the database to create a new user record
         */
        $newUser = [
            'name'      => $name,
            'email'     => $email,
            'address'   => $address,
            'city'      => $city,
            'state'     => $state,
            'zip'       => $zip,
            'dayphone'  => $dayphone,
            'evephone'  => $evephone,
            'password'  => $password
        ];


        if(createNewUser($db, $newUser)){
            $_SESSION['id'] = $db->lastInsertId();
            $_SESSION['loggedin'] = true;
            $_SESSION['message'] = "You have successfully registered and have automatically been logged in.";
            die(header('Location:/members'));
        } else {
            $errors['general'] = "Sorry, there was an error completing your registration. Please try again later.";
            $_SESSION['registration_errors'] = $errors;
            die(header('Location: /register'));
        }

    } else {
        $_SESSION['registration_errors'] = $errors;
        die(header('Location:/register'));
    }


}  else {
    die(header('Location:/register'));
}