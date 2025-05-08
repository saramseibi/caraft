<?php
// Initialize the session
session_start();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true;
}

// Function to get user's name if logged in
function getUserName() {
    if (isLoggedIn() && isset($_SESSION["name"])) {
        return $_SESSION["name"];
    }
    return false;
}

// Function to get user's email if logged in
function getUserEmail() {
    if (isLoggedIn() && isset($_SESSION["email"])) {
        return $_SESSION["email"];
    }
    return false;
}
?>