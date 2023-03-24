<?php

/**
 * The MIT License (MIT)
 * 
 * Copyright (c) 2014-2015 Ron Bodnar <rbodnar93@gmail.com>

 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * */
define('host', 'localhost');
define('username', '');
define('password', '');
define('database', '');

class Database {

    private $connection;

    function __construct() {
        try {
            $this->connection = new PDO('mysql:host=' . host . ';dbname=' . database, username, password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo '<strong>ERROR: ' . $e->getMessage() . '</strong><br />';
        }
    }

    function create_table() {
        $query = 'CREATE TABLE users(username varchar (255) NOT NULL UNIQUE KEY, password varchar (255) NOT NULL, email varchar (255) NOT NULL)';
        $statement = $this->connection->prepare($query);
        $statement->execute();
    }

    function get_user($username) {
        try {
            $statement = $this->connection->prepare('SELECT * FROM users WHERE username = :username');
            $statement->execute(array('username' => $username));
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            $result = $statement->fetch();
            $user = array(
                'username' => $result['username'],
                'password' => $result['password'],
                'email' => $result['email']
            );
            //return json_encode($user);
            return new User($result['username'], $result['password'], $result['email']);
        } catch (PDOException $e) {
            echo '<strong>Error getting user data for <em>' . $username . '</em>:<br /> ' . $e->getMessage() . '</strong><br />';
        }
    }

    function create_user($username, $password, $email) {
        try {
            $statement = $this->connection->prepare('INSERT INTO users(username, password, email) VALUES(:username, :password, :email)');
            $statement->execute(array(
                ':username' => $username,
                ':password' => $password,
                ':email' => $email
            ));
        } catch (PDOException $e) {
            echo '<strong>Error creating user for <em>' . $username . '</em>:<br /> ' . $e->getMessage() . '</strong><br />';
        }
    }

    function update_user($user) {
        if (!($user instanceof User)) {
            echo 'failed to update user, the specified user is invalid:<br />';
            echo $user . '<br />';
            return;
        }
        try {
            $statement = $this->connection->prepare('UPDATE users SET username=:username, password=:password, email=:email WHERE username=:username');
            $statement->bindParam(':username', $user->username);
            $statement->bindParam(':password', $user->password);
            $statement->bindParam(':email', $user->email);
            $statement->execute();
        } catch (PDOException $e) {
            echo '<strong>Error updating user data for <em>' . $user->username . '</em>:<br /> ' . $e->getMessage() . '</strong><br />';
        }
    }

    function delete_user($username) {
        try {
            $statement = $this->connection->prepare('DELETE FROM users WHERE username = :username');
            $statement->bindParam(':username', $username);
            $statement->execute();
        } catch (PDOException $e) {
            echo '<strong>Error deleting user data for <em>' . $username . '</em>:<br /> ' . $e->getMessage() . '</strong><br />';
        }
    }

}

?>