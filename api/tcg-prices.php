<?php

/**
 * The MIT License (MIT)
 * 
 * Copyright (c) 2014 Ron Bodnar <rbodnar93@gmail.com>

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
require 'Database.class.php';

define('USERNAME', '');
define('PASSWORD', '');
define('PARTNER_KEY', '');

$cardName = $_GET['cardName'];
$cardSet = $_GET['cardSet'];
$action = $_GET['action'];
if ($action === 'getprice') {
    if (isset($cardName) && isset($cardSet)) {
        $price = get_price($cardName, $cardSet);
        $response = array(
            'low' => $price['product']['lowprice'],
            'avg' => $price['product']['avgprice'],
            'high' => $price['product']['hiprice'],
            'foilAvg' => $price['product']['foilavgprice'],
            'storeURL' => $price['product']['link'],
        );
        die(json_encode($response));
    }
}

$testCards = array(
    'Avacyn\'s Pilgrim-ISD' => array(
        'name' => 'Avacyn\'s Pilgrim',
        'cost' => '(G)',
        'color' => 'Green',
        'edition' => array(
            'set' => 'Innistrad',
            'setID' => 'ISD',
            'image' => 'https://image.deckbrew.com/mtg/multiverseid/243212.jpg',
            'price' => array(
                'low' => '',
                'average' => '',
                'high' => '',
                'foil' => ''
            ),
            'rarity' => 'common'
        ),
        'quantity' => '4'
    )
);

$db = new Database();

function get_compiled_url($name, $setName, $store = false) {
    $parameters = array(
        'pk' => PARTNER_KEY, // partner key
        'p' => $name, // card name
        's' => $setName, // set name
        'v' => '5', // vendor count
    );
    $url = $store ? 'http://partner.tcgplayer.com/x3/pv.asmx/p?' : 'http://partner.tcgplayer.com/x3/phl.asmx/p?';
    foreach ($parameters as $key => $value) {
        if (strcmp($key, 'v') === 0 && !$store) {
            continue;
        }
        $url .= $key . '=' . urlencode($value) . '&';
    }
    return rtrim($url, '&');
}

function get_price($name, $setName) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, get_compiled_url($name, $setName));
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
        $data = json_decode(json_encode(simplexml_load_string($response)), true);
        return $data;
    }
    return array('lol' => 'error');
}

?>