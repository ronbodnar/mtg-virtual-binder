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
 **/
require 'Database.class.php';
require '/home/aeterna/public_html/mtg/functions.php';

$db = new Database();
$db->create_user('sean', '', '');
$user = $db->get_user('sean');

if ($user->binders == null || count($user->binders) <= 0) {
    /*
     * Test
     */
    /*$user->binders = array(
        'wishlist' => array('name' => 'wishlist', 'index' => '1', 'cards' => load_xml('jay/wishlist')),
        'common' => array('name' => 'common', 'index' => '2', 'cards' => load_xml('jay/common')),
        'rare' => array('name' => 'rare', 'index' => '3', 'cards' => load_xml('jay/rare')),
        'foil' => array('name' => 'foil', 'index' => '4', 'cards' => load_xml('jay/foil'))
    );*/
    
    /*
     * Jay
     */
    /*$user->binders = array(
        'wishlist' => array('name' => 'wishlist', 'index' => '1', 'cards' => load_xml('jay/wishlist')),
        'common' => array('name' => 'common', 'index' => '2', 'cards' => load_xml('jay/common')),
        'rare' => array('name' => 'rare', 'index' => '3', 'cards' => load_xml('jay/rare')),
        'foil' => array('name' => 'foil', 'index' => '4', 'cards' => load_xml('jay/foil'))
    );*/
    
    /*
     * Sean "Huddy" Hudson
     */
    $user->binders = array(
        'wishlist' => array('name' => 'wishlist', 'index' => '1', 'cards' => load_xml('sean/new/wishlist')),
        'common' => array('name' => 'common', 'index' => '2', 'cards' => load_xml('sean/new/common')),
        'uncommon' => array('name' => 'uncommon', 'index' => '2', 'cards' => load_xml('sean/new/uncommon')),
        'rare' => array('name' => 'rare', 'index' => '3', 'cards' => load_xml('sean/new/rare')),
        'mythic' => array('name' => 'mythic', 'index' => '3', 'cards' => load_xml('sean/new/mythic')),
        'foil' => array('name' => 'foil', 'index' => '4', 'cards' => load_xml('sean/new/foil'))
    );
    write_binder_file();
}

function load_xml($binder) {
    $cards = array();

    $document = new DOMDocument();
    $document->load('binder_xml/' . $binder . '.xml');

    $cardElements = $document->getElementsByTagName("card");
    foreach ($cardElements as $element) {
        $name = $element->getElementsByTagName("name")->item(0)->nodeValue;
        $cost = $element->getElementsByTagName("cost")->item(0)->nodeValue;
        $color = $element->getElementsByTagName("color")->item(0)->nodeValue;
        $quantity = $element->getElementsByTagName("quantity")->item(0)->nodeValue;

        $set = $element->getElementsByTagName("set")->item(0)->nodeValue;
        $setID = $element->getElementsByTagName("setID")->item(0)->nodeValue;
        $image = $element->getElementsByTagName("image")->item(0)->nodeValue;
        $rarity = $element->getElementsByTagName("rarity")->item(0)->nodeValue;

        $priceArray = array();
        $prices = $element->getElementsByTagName("prices");
        foreach ($prices as $price) {
            $low = $price->getElementsByTagName("low")->item(0)->nodeValue;
            $median = $price->getElementsByTagName("median")->item(0)->nodeValue;
            $high = $price->getElementsByTagName("high")->item(0)->nodeValue;
            $priceArray = array('low' => $low, 'median' => $median, 'high' => $high);
        }
        $cards = $cards + array(
            $name . '-' . $setID => array(
                'name' => $name,
                'cost' => $cost,
                'color' => $color,
                'edition' => array(
                    'set' => $set,
                    'setID' => $setID,
                    'image' => $image,
                    'price' => $priceArray,
                    'rarity' => $rarity
                ),
                'quantity' => $quantity
            )
        );
    }
    return $cards;
}
?>