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
require('User.class.php');
require('BrowserDetection.class.php');

$user = new User('Ron', '', '');
$results = array();

$browser = new Browser();

$source = $_POST['source'];
$cardName = trim($_POST['card_name']);
$submitted = isset($_POST['submit']);

$rootIPs = array(
);

query_database();
populate_binders();

if ($submitted) {
    if ($_POST['submit'] == 'Search') {
        collect_data();
    } else if ($_POST['submit'] == 'Submit' && isset($_POST['count']) && has_root_access()) {
        $count = $_POST['count'];
        for ($i = 0; $i < $count; $i++) {
            $binder = strtolower($_POST['binder-' . $i]);
            if (!array_key_exists($binder, $user->binders)) {
                echo 'binder blocked: ' . $binder . '<br />';
                continue;
            }
            $quantity = $_POST['quantity-' . $i];
            if ($quantity == '0' && $source === 'search') {
                continue;
            }
            $card = unserialize($_POST['card-' . $i]);
            if ($card == null || !$card['name'] || $card['name'] == null || $card['edition'] == null) {
                $keys = array_keys($user->binders[$binder]['cards']);
                echo 'card blocked: ' . $i . ' [' . $keys[$i] . ']<br />';
                unset($user->binders[$binder]['cards'][$keys[$i]]);
                continue;
            }
            $card['quantity'] = $quantity;
            $identifier = $card['name'] . '-' . $card['edition']['setID'];
            $sameEdition = $user->binders[$binder]['cards'][$identifier]['edition']['setID'] === $card['edition']['setID'];
            if ($source === 'search') {
                if ($sameEdition) {
                    $user->binders[$binder]['cards'][$identifier]['quantity'] += $quantity;
                } else {
                    $user->binders[$binder]['cards'] = $user->binders[$binder]['cards'] + array($identifier => $card);
                }
            } else {
                $movedBinders = strcmp($source, $binder) !== 0;
                if ($quantity == 0) {
                    unset($user->binders[$binder]['cards'][$identifier]);
                } else {
                    if ($movedBinders) {
                        if (array_key_exists($identifier, $user->binders[$binder]['cards'])) {
                            $user->binders[$binder]['cards'][$identifier]['quantity'] += $quantity;
                        } else {
                            $user->binders[$binder]['cards'] = $user->binders[$binder]['cards'] + array($identifier => $card);
                        }
                        unset($user->binders[$source]['cards'][$identifier]); // remove from previous binder
                    } else {
                        $user->binders[$binder]['cards'][$identifier]['quantity'] = $quantity;
                    }
                }
            }
        }
        write_binder_file();
    }
}

function add_binder($name, $cards = array()) {
    global $user;
    if (array_key_exists($name, $user->binders)) {
        return;
    }
    $user->binders = $user->binders + array(
        $name => array(
            'name' => $name,
            'index' => 1,
            'cards' => $cards
        )
    );
    write_binder_file();
}

function remove_binder($name) {
    global $user;
    if (!$user || $user == null || strlen($user->username) <= 0) {
        return;
    }
    if (!array_key_exists($name, $user->binders)) {
        return;
    }
    unset($user->binders[$name]);
    write_binder_file();
}

function query_database() {
    global $user;
    $fields = array(
        'action' => 'get_user',
        'username' => urlencode('jay'),
        'password' => urlencode('')
    );
    $fieldString = '';
    foreach ($fields as $key => $value) {
        $fieldString .= $key . '=' . $value . '&';
    }
    rtrim($fieldString, '&');

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://mron.dev/projects/mtg-binder/api/mtg-database.php');
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldString);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if ($response === false) {
        $error = '#' . curl_errno($ch) . ' - ' . curl_error($ch);
    }
    curl_close($ch);

    if ($error) {
        echo 'cURL error: ' . $error . '<br />';
    } else {
        $response = json_decode($response, true);
        $user = new User($response['username'], $response['password'], $response['email']);
    }
}

function populate_binders() {
    global $user;
    $binders = get_binder_file_contents();
    $user->binders = json_decode($binders, true);
}

function get_binder_file_contents() {
    global $user;
    if (!$user || $user == null || strlen($user->username) <= 0) {
        return;
    }
    if (!file_exists('user.binder')) {
        $handle = fopen('user.binder', 'w') or die('Error while creating binder file for: ' . $user->username);
        fclose($handle);
    }
    $file = fopen('user.binder', 'r') or die('Error while reading binder file for: ' . $user->username);
    $fileSize = filesize('user.binder');
    if ($fileSize <= 0) {
        fclose($file);
        return 'N/A';
    } else {
        $contents = fread($file, $fileSize);
        fclose($file);
        return $contents;
    }
}

function write_binder_file() {
    global $user;
    if (!$user || $user == null || strlen($user->username) <= 0) {
        return;
    }
    $file = fopen('user.binder', 'w') or die('Error while writing binder file for: ' . $user->username);
    fwrite($file, json_encode($user->binders));
    fclose($file);
}

function get_mana_images($cost) {
    $images = array();
    $matches = array();
    preg_match_all("#\((.*?)\)#", $cost, $matches);
    foreach ($matches[1] as &$match) {
        array_push($images, 'http://cdn.mron.co/images/mtg/mana/' . strtolower($match) . '.png');
    }
    return $images;
}

function sql() {
    
}

function collect_data() {
    global $results, $cardName;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.deckbrew.com/mtg/cards?name=' . str_replace(' ', '+', $cardName));
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    if ($response === false) {
        $error = '#' . curl_errno($ch) . ' - ' . curl_error($ch);
    }
    curl_close($ch);
    if ($error) {
        echo 'cURL error: ' . $error . '<br />';
    } else {
        $response = json_decode($response, true);
        foreach ($response as &$res) {
            $editionArray = array();
            $editions = $res['editions'];
            foreach ($editions as &$edition) {
                array_push($editionArray, array(
                    'id' => '',
                    'set' => $edition['set'],
                    'setID' => $edition['set_id'],
                    'image' => $edition['image_url'],
                    'price' => array(
                        'low' => '',
                        'avg' => '',
                        'high' => '',
                        'foilAvg' => '',
                        'storeURL' => ''
                    ),
                    'rarity' => $edition['rarity']
                ));
            }
            $name = $res['name'];
            $cost = strlen($res['cost']) <= 0 ? 'N/A' : str_replace(array('{', '}'), array('(', ')'), $res['cost']);
            $types = $res['types'];
            if (count($types) == 0) {
                $type = 'N/A';
            } else if (count($types) == 1) {
                $type = ucfirst($types[0]);
            } else if (count($types) > 1) {
                $type = 'multi?';
            }
            $colors = $res['colors'];
            if (count($colors) == 0) {
                $color = 'N/A';
            } else if (count($colors) == 1) {
                $color = ucfirst($colors[0]);
            } else if (count($colors) > 1) {
                $color = 'Gold';
            }
            foreach ($editionArray as &$edition) {
                array_push($results, array(
                    'id' => $edition['id'],
                    'name' => $name,
                    'cost' => $cost,
                    'type' => $type,
                    'color' => $color,
                    'edition' => $edition,
                    'quantity' => -1
                ));
            }
        }
    }
}

function get_user_ip() {
    $remote = $_SERVER['REMOTE_ADDR'];
    $client = $_SERVER['HTTP_CLIENT_IP'];
    $forward = $_SERVER['HTTP_X_FORWARDED_FOR'];
    if (filter_var($client, FILTER_VALIDATE_IP)) {
        $ip = $client;
    } else if (filter_var($forward, FILTER_VALIDATE_IP)) {
        $ip = $forward;
    } else {
        $ip = $remote;
    }
    return $ip;
}

function has_root_access() {
    global $rootIPs;
    return in_array(get_user_ip(), $rootIPs);
}

?>