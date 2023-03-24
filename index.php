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
require 'functions.php';
?>
<html>
    <head>
        <title>MTG Virtual Binders</title>

        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel='icon' type='image/x-icon' href='favicon.ico' />

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" />
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css" />

        <!-- Main page CSS -->
        <link rel='stylesheet' href='resources/css/style.css' />

        <!-- Plugin CSS -->
        <link rel='stylesheet' href='resources/css/hover.css' />
        <link rel='stylesheet' href='resources/css/qtip.min.css' />
    </head>

    <body>
        <div id="content-page">
            <div class="banner-top">
                <a href="."><img src="resources/background-carbon.png" alt="Banner" class="img-responsive" width="1000" height="179" /></a>
            </div>

            <div class="modal fade" id="addTabModal" tabindex="-1" role="dialog" aria-labelledby="addTabModal" aria-hidden="true">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header" style="padding: 8px; text-align: center;">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                            <h4 class="modal-title">Name your new binder!</h4>
                        </div>
                        <div class="modal-body" style="text-align: center;">
                            <input type="text" id="binder-name-field" placeholder="Enter a binder name" />
                        </div>
                        <div class="modal-footer" style="text-align: center; padding: 8px; border-top: none;">
                            <button type="button" class="btn btn-xs btn-success" id="modal-button">Add Binder</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- start of page content -->
            <div id="container">
                <!-- start of alerts -->
                <?php if ($submitted && $_POST['submit'] === 'Submit') { ?>
                    <div class="alert alert-success" id="closable-alert auto-close-alert" style="text-align: center; width: 50%;">
                        <a class="close" data-dismiss="alert">x</a>
                        Your binders have been successfully updated!
                    </div>
                <?php } ?>

                <?php if (!$user || !$user->binders || count($user->binders) <= 0) { ?>
                    <div class="alert alert-danger" id="closable-alert auto-close-alert" style="text-align: center; width: 50%;">
                        <a class="close" data-dismiss="alert">x</a>
                        The user <?php echo $userParameter; ?> was not found
                    </div>
                <?php } ?>
                <!-- end of alerts -->

                <!-- start of navigation tabs -->
                <ul id="tab-container" class="nav nav-tabs" role="tablist">
                    <li class="active"><a href="#general" role="tab" data-toggle="tab">General</a></li>
                    <?php
                    if ($user->binders && count($user->binders) > 0) {
                        $buttonString = has_root_access() ? "<button class='close' title='Remove this binder' type='button'>x</button>" : "";
                        foreach ($user->binders as $key => $value) {
                            echo "<li><a href='#" . strtolower(str_replace(' ', '-', $key)) . "' role='tab' data-toggle='tab'>" . ucwords(str_replace('-', ' ', $key)) . "" . $buttonString . "</a></li>";
                        }
                    }
                    ?>
                    <?php
                    if (has_root_access()) {
                        echo '<li><a href="#add-tab" id="btnAddPage" data-toggle="modal" data-target="#addTabModal" title="Add a new binder"><span class="glyphicon glyphicon-plus" id="add-button"></span></a></li>';
                    }
                    ?>
                </ul> <!-- end of navigation tabs -->

                <!-- start tab contents -->
                <div class="tab-content">
                    <!-- start of general tab -->
                    <div class="tab-pane fade in active" id="general" align="center">
                        <?php
                        $totalCards = 0;
                        $totalValue = 0;
                        if ($user->binders && count($user->binders) > 0) {
                            foreach ($user->binders as &$binder) {
                                $totalCards += count($binder['cards']);
                            }
                            echo '<div style="text-align: left; margin-left: 10px;">';
                            echo 'Binders: ' . count($user->binders) . '<br />';
                            echo 'Total cards: ' . number_format($totalCards) . '<br />';
                            echo '</div>';
                            echo '<hr />';
                        }
                        ?>

                        <h4>Card Database Search</h4>

                        <br />

                        <!-- start of card database search -->
                        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                            <input type="text" name="card_name" placeholder='Enter a card name'/>
                            <input type="submit" name="submit" value="Search" />
                            <?php if ($submitted && $_POST['submit'] === 'Search') { ?>
                                <br /><br />
                                <table id="search-table" class="table-curved themed-table tablesorter"> 
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th style="text-align: center;">Cost</th>
                                            <th style="text-align: center;">Color</th>
                                            <th style="text-align: center;">Set</th>
                                            <th style="text-align: center;">Price</th>
                                            <?php if (has_root_access()) { ?>
                                                <th style="text-align: center;" class='{sorter: false}'>Quantity</th>
                                                <th style="text-align: center;" class='{sorter: false}'>Binder</th>
                                            <?php } ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $count = count($results);
                                        for ($i = 0; $i < $count; $i++) {
                                            $result = $results[$i];
                                            $manaImages = get_mana_images(str_replace('/', '-', $result['cost']));
                                            $prices = array(
                                                'low' => $result['edition']['price']['low'],
                                                'median' => $result['edition']['price']['median'],
                                                'high' => $resul['edition']['price']['high']
                                            );
                                            echo '<tr qtip-content="' . $result['edition']['image'] . '">';
                                            echo '<td>' . $result['name'] . '</td>';
                                            echo '<td>';
                                            if (!$result['cost'] || strlen($result['cost']) <= 0 || $result['cost'] === '' || $result['cost'] === 'N/A') {
                                                echo $result['type'];
                                            } else {
                                                for ($a = 0; $a < count($manaImages); $a++) {
                                                    $image = $manaImages[$a];
                                                    echo '<img src="' . $image . '" width="' . ($browser->isMobile() ? '12' : '16') . '" height="' . ($browser->isMobile() ? '12' : '16') . '" />';
                                                    if ($a == 3 || $a == 7) {
                                                        echo '<br />';
                                                    }
                                                }
                                            }
                                            echo '</td>';
                                            echo '<td>' . $result['color'] . '</td>';
                                            echo '<td>' . ($result['edition'] == null || strlen($result['edition']['setID']) <= 0 ? 'N/A' : $result['edition']['setID']) . '</td>';
                                            echo '<td>' . ($prices['median'] == null ? 'N/A' : '<font color="green">$' . number_format(round(($prices['median'] / 100), 2), 2) . '</font>') . '</td>';
                                            if (has_root_access()) {
                                                echo '<td><input name="quantity-' . $i . '" type="text" style="width: 30px;" value="0" /></td>';
                                                echo '<td><select name="binder-' . $i . '">';
                                                foreach ($user->binders as $key => $value) {
                                                    echo '<option value="' . strtolower($key) . '">' . ucwords(str_replace('-', ' ', $key)) . '</option>';
                                                }
                                                echo '</select></td>';
                                            }
                                            echo '</tr>';
                                            echo '<input type="hidden" name="card-' . $i . '" value="' . htmlspecialchars(serialize($result), ENT_QUOTES) . '" />';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                                <input type='hidden' name='count' value='<?php echo $count; ?>' />
                                <br />
                                <input type='hidden' name='source' value='search' />
                                <?php if (has_root_access()) { ?>
                                    <input type="submit" name="submit" value="Submit" />
                                    <?php
                                }
                            }
                            ?>
                        </form> <!-- end of card database search -->
                    </div><!-- end of general tab -->

                    <!-- start of custom defined binder tabs -->
                    <?php
                    if ($user->binders && count($user->binders) > 0) {
                        foreach ($user->binders as $key => $value) {
                            $name = $value['name'];
                            $index = $value['index'];
                            $cards = $value['cards']; // this is an array
                            ?>
                            <div class="tab-pane fade" id='<?php echo strtolower(str_replace(' ', '-', $name)); ?>'>
                                <form method="POST" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                                    <?php
                                    if (count($cards) <= 0) {
                                        echo 'There are currently no cards in this binder.';
                                    } else {
                                        ?>
                                        <table id="binder-table-<?php echo strtolower(str_replace(' ', '-', $name)); ?>" class="themed-table tablesorter"> 
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th style="text-align: center;">Cost</th>
                                                    <th style="text-align: center;">Color</th>
                                                    <th style="text-align: center;">Set</th>
                                                    <th style="text-align: center;">Price</th>
                                                    <?php if (has_root_access()) { ?>
                                                        <th style="text-align: center;" class='{sorter: false}'>Rarity</th>
                                                    <?php } ?>
                                                    <th style="text-align: center; width: 12%;"><?php echo $browser->isMobile() ? 'Qty' : 'Quantity'; ?></th>
                                                    <?php if (has_root_access()) { ?>
                                                        <th style="text-align: center;" class='{sorter: false}'>Binder</th>
                                                    <?php } ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $totalCards = 0;
                                                $totalPrice = 0.00;
                                                $count = count($cards);
                                                for ($i = 0; $i < $count; $i++) {
                                                    $keys = array_keys($cards);
                                                    $card = $cards[$keys[$i]];

                                                    $manaImages = get_mana_images(str_replace('/', '-', $card['cost']));
                                                    $prices = array(
                                                        'low' => $card['edition']['price']['low'],
                                                        'median' => $card['edition']['price']['median'],
                                                        'high' => $card['edition']['price']['high']
                                                    );
                                                    $totalCards += $card['quantity'];
                                                    $totalPrice += ($prices['median'] * $card['quantity']);

                                                    echo '<tr qtip-content="' . $card['edition']['image'] . '">';
                                                    echo '<td><a href="#" style="text-decoration: underline;" target="_blank">' . $card['name'] . '<a/></td>';
                                                    echo '<td>';
                                                    if (!$card['cost'] || strlen($card['cost']) <= 0 || $card['cost'] === '' || $card['cost'] === 'N/A') {
                                                        echo $card['type'];
                                                    } else {
                                                        for ($a = 0; $a < count($manaImages); $a++) {
                                                            $image = $manaImages[$a];
                                                            echo '<img src="' . $image . '" width="' . ($browser->isMobile() ? '12' : '16') . '" height="' . ($browser->isMobile() ? '12' : '16') . '" />';
                                                            if ($a == 3 || $a == 7) {
                                                                echo '<br />';
                                                            }
                                                        }
                                                    }
                                                    echo '</td>';
                                                    echo '<td>' . ucfirst($card['color']) . '</td>';
                                                    echo '<td>' . (($card['edition'] == null || strlen($card['edition']['set']) <= 0) ? 'N/A' : $card['edition']['set']) . '</td>';// was setID
                                                    if ($name === 'foil') {
                                                        echo '<td>TBD</td>';
                                                    } else {
                                                        echo '<td>' . ($prices['median'] == null ? 'N/A' : '<font color="green">$' . number_format(round(($prices['median'] / 100), 2), 2) . '</font>') . '</td>';
                                                    }
                                                    if (has_root_access()) {
                                                        echo '<td>' . ucfirst($card['edition']['rarity']) . '</td>';
                                                    }
                                                    if (!$card['quantity'] || $card['quantity'] <= 0) {
                                                        $card['quantity'] = '?';
                                                    }
                                                    echo '<td>';
                                                    if (has_root_access()) {
                                                        echo '<input type="hidden" name="binder-' . $i . '" value="' . $name . '" />';
                                                        echo '<input type="hidden" name="card-' . $i . '" value="' . htmlspecialchars(serialize($card)) . '" />';
                                                        echo '<input name="quantity-' . $i . '" type="text" style="width: 30px;" value="' . $card['quantity'] . '" />';
                                                    } else {
                                                        echo $card['quantity'];
                                                    }
                                                    echo '</td>';

                                                    if (has_root_access()) {
                                                        echo '<td><select name="binder-' . $i . '">';
                                                        foreach ($user->binders as $key => $value) {
                                                            $selected = $key === $name ? ' selected' : '';
                                                            echo '<option value="' . strtolower($key) . '"' . $selected . '>' . ucwords(str_replace('-', ' ', $key)) . '</option>';
                                                        }
                                                        echo '</select></td>';
                                                    }
                                                    echo '</tr>';
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                        <br />
                                        <div style="text-align: left; margin-left: 10px;">
                                            Binder value: <font color="green">$<?php echo number_format(round(($totalPrice / 100), 2), 2); ?></font><br /><br />
                                            # of total cards: <font color="orange"><?php echo $totalCards; ?></font><br />
                                            # of unique cards: <font color="orange"><?php echo count($cards); ?></font>
                                            <br /><br />
                                            <?php
                                            if (has_root_access()) {
                                                echo '<input type="hidden" name="source" value="' . $name . '" />';
                                                echo '<input type="hidden" name="binder" value="' . $name . '" />';
                                                echo '<input type="hidden" name="count" value="' . $count . '" />';
                                                echo '<input type="submit" name="submit" value="Submit" />';
                                            }
                                            ?>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                </form>
                            </div> <!-- end of custom defined binder tabs -->
                            <?php
                        }
                    }
                    ?>
                </div> <!-- end of tab contents -->
            </div> <!-- end of page content -->
        </div> <!-- end of content-page -->

        <div id="footer">
            <!-- &#8212; -->
            <span style="float: left; text-align: center;">
                Developed by Ron Bodnar
                <br />
                Pricing data provided by <a href="http://tcgplayer.com">TCGPlayer</a>
            </span>
            <span style="float: right; text-align: center;">
                Copyright (&copy;) 2014-2015 <a href="http://mron.dev/" target="_blank">MRon Development</a>
                <br />
                All images Copyright (&copy;) 1995-2015 <a href="https://company.wizards.com/" target="_blank">Wizards of the Coast</a>
            </span>
        </div>

        <!-- jQuery & jQuery Plugins -->
        <script type='text/javascript' src="//code.jquery.com/jquery-2.1.1.min.js"></script>
        <script type='text/javascript' src='resources/js/jquery/jquery.qtip.min.js'></script>
        <script type='text/javascript' src='resources/js/jquery/jquery.metadata.js'></script>
        <script type='text/javascript' src="resources/js/jquery/jquery.hashchange.min.js"></script>
        <script type='text/javascript' src='resources/js/jquery/jquery.tablesorter.min.js?v=1'></script>

        <!-- Bootstrap -->
        <script type='text/javascript' src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>

        <!-- Custom -->
        <script type='text/javascript' src='resources/js/binder-core.js'></script>
    </body>
</html>