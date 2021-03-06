<?php

set_time_limit(120);

function _compo2_rate($params) {
    if (!$params["uid"]) {
        echo "<p class='message'>You must sign in to vote.</p>";
        return _compo2_preview($params);
    }
    
//    echo "<!--";
//    print_r($params);
//    echo "-->";

    // handle non-competitors ..
    $ce = compo2_entry_load($params["cid"],$params["uid"]);
    if (!intval($params["pubvote"]))
    if ((!$ce["id"]) || (!$ce["active"])) {
        $action = isset($_REQUEST["action"])?$_REQUEST["action"]:"preview";
        if ($action == "default") { $action = "preview"; }
        if ($action == "edit") {
            return _compo2_active_form($params);
        } elseif ($action == "save") {
            return _compo2_active_save($params);
        } elseif ($action == "me") {
            _compo2_preview_me($params);
        } elseif ($action == "preview") {
            echo "<p class='message'>Voting is only available to participants.</p>";
            if (!isset($params["locked"])) {
         		if ( ((is_array($params["opendivs"])) && (count($params["opendivs"]) > 0)) || trim($params["opendivs"]) !== "" ) {
            		echo "<p><a href='?action=edit'>Submit an Entry</a></p>";
            	}
            }
            return _compo2_preview($params);
        } elseif ($action == "rate") {
            header("Location: ./?action=preview&uid=".intval($_REQUEST["uid"])); die;
        }
        return;
    }

    $action = isset($_REQUEST["action"])?$_REQUEST["action"]:"default";
    if ($action == "default") {
        return _compo2_rate_list($params);
    } elseif ($action == "preview") { // send user to rate page
        if (isset($_REQUEST["uid"])) {
            return _compo2_rate_rate($params);
        } else {
            echo "<p><a href='?action=default'>Back to Rate Entries</a></p>";
            return _compo2_preview($params,"?action=preview");
        }
/*    } elseif ($action == "comments") {
        return _compo2_rate_comments($params);*/
    } elseif ($action == "me") {
        _compo2_preview_me($params);
    } elseif ($action == "rate") { // deprecated, but left here to keep old links live
        return _compo2_rate_rate($params);
    } elseif ($action == "submit") {
        return _compo2_rate_submit($params);
    } elseif ($action == "edit") {
        return _compo2_active_form($params);
    } elseif ($action == "save") {
        return _compo2_active_save($params);
    }
}

// this shows the archive of old comments before we created the c2_comments table to store comments
function _compo2_show_comments($cid,$uid) {
    $r = compo2_query("select * from c2_rate where cid = ? and to_uid = ? and length(comments) > 1 order by ts asc",array($cid,$uid));
    if (!count($r)) { return; }
    echo "<h2>Comments (archive)</h2>";
    foreach ($r as $ve) if (strlen(trim($ve["comments"]))) {
        $user = compo2_get_user($ve["from_uid"]);
        echo "<h4>{$user->display_name} says ...</h4>";
        echo "<p>".str_replace("\n","<br/>",htmlentities($ve["comments"]))."</p>";
    }
}

// function _compo2_rate_comments($params) {
// //     return _compo2_rate_rate($params,$params["uid"]);
//     header("Location: ?action=preview&uid={$params["uid"]}"); die;
// }


function _compo2_rate_sort($a,$b) {
    return strcmp($a["s"],$b["s"]);
}

function _compo2_rate_sort_by_rate_in($a,$b) {
    return $a["rate_in"] - $b["rate_in"];
}
function _compo2_rate_sort_by_rate_out($a,$b) {
    return $b["rate_out"] - $a["rate_out"];
}

function add_quotes($str) {
    return sprintf("'%s'", $str);
}

function _compo2_rate_list($params) {
    @$q = $_REQUEST["q"];
    
    if ( isset($params['ratedivs']) && trim($params['ratedivs']) !== "" ) {
    	$ratedivs = explode(";",$params['ratedivs']);
    }
    else {
	    $ratedivs = ["compo","open"];
	}

	$ratedivs_out = implode(",", array_map('add_quotes',$ratedivs));
    
    $ecnt = array_pop(compo2_query("select count(*) cnt from c2_entry where cid = ? AND active = 1 AND is_judged = 1 AND etype IN ({$ratedivs_out})",array($params["cid"])));
    //$ecnt = array_pop(compo2_query("select count(*) cnt from c2_entry where cid = ? AND active = 1 AND is_judged = 1",array($params["cid"])));
    $cnt = $ecnt["cnt"];

    if (!strlen($q)) {
        $_r = compo2_query("select etype,shots,title,uid,cid,rate_in,rate_out,get_user,settings from c2_entry where cid = ? AND active = 1 AND is_judged = 1 AND etype IN ({$ratedivs_out})",array($params["cid"]));
        //$_r = compo2_query("select etype,shots,title,uid,cid,rate_in,rate_out,get_user from c2_entry where cid = ? AND active = 1 AND is_judged = 1",array($params["cid"]));
    } else {
        $_r = compo2_query("select etype,shots,title,uid,cid,rate_in,rate_out,get_user,settings from c2_entry where (title like ? OR notes like ? OR links like ? OR get_user like ?) AND cid = ? AND active = 1 AND is_judged = 1 AND etype IN ({$ratedivs_out})",array("%$q%","%$q%","%$q%","%$q%",$params["cid"]));
        //$_r = compo2_query("select etype,shots,title,uid,cid,rate_in,rate_out,get_user from c2_entry where (title like ? OR notes like ? OR links like ? OR get_user like ?) AND cid = ? AND active = 1 AND is_judged = 1",array("%$q%","%$q%","%$q%","%$q%",$params["cid"]));
        $_REQUEST["more"] = 1;
    }
    
    $r_rate = array();
    foreach (compo2_query("select * from c2_rate where cid = ? and from_uid = ?",array($params["cid"],$params["uid"])) as $ve) {
        $r_rate[$ve["to_uid"]] = $ve;
    }
    
    $sortby = isset($_REQUEST["sortby"])?$_REQUEST["sortby"]:"default";
    
    $r_rated = array();
    $r_unrated = array();
    $total = count($_r);
    foreach ($_r as $k=>$ce) {
        $ce["rate_c"] = compo2_calc_coolness($ce["rate_out"],$cnt);
        if (isset($r_rate[$ce["uid"]])) {
            $ue = unserialize($ce["get_user"]);
            $key = strtolower($ue["display_name"]);
            $r_rated[$key] = $ce;
        } else {
        	$ce['settings'] = unserialize($ce['settings']);
        	
        	$ce["legal_cats"] = 8;
        	if ( isset($ce['settings']['OPTOUT']) ) {
      		  	if ( isset($ce['settings']['OPTOUT'][$ce['etype']]) ) {
        			foreach( $ce['settings']['OPTOUT'][$ce['etype']] as $lcat ) {
        				if ( $lcat > 0 ) {
        					$ce["legal_cats"]--;
        				}
        			}
        		}
        	}

//        	echo "<!-- FEEB: ";
//        	print_r( $ce );
//			echo "\nCats: " . $ce["legal_cats"];
//        	echo "-->\n";
        	
            $ce["rate_d"] = ($ce["rate_in"] + 50 - (sqrt(min(100,$ce["rate_out"])) * 50 / 10));
            if ($sortby == "ratings") {
                $v = $ce["rate_in"];
            } elseif ($sortby == "coolness") {
                $v = - $ce["rate_out"];
            } else {
                $v = $ce["rate_d"];
            }

			if ( $ce["legal_cats"] == 0 ) {
				continue;
			}

            $key = sprintf("%05d|%s",10000+$v,$ce["uid"]);
            $r_unrated[$key] = $ce;
        }
    }
    ksort($r_rated);
    ksort($r_unrated);
    
    echo "<h3>Rate Entries (".count($_r).")</h3>";
    
    if (isset($_REQUEST["dump"])) {
        echo "<h3>dump-only includes entries you haven't rated</h3>";
        echo "<table><tr><th><th>D<th>R<th>C";
        foreach ($r_unrated as $e) {
            $ue = unserialize($e["get_user"]);
            echo "<tr><th>";
            echo $ue["display_name"];
            $rate_in = intval($e["rate_in"]);
            $rate_out = intval($e["rate_out"]);
            $rate_d = intval($e["rate_d"]);
            echo "<td>$rate_d<td>$rate_in<td>$rate_out";   
        }
        echo "</table>";
    }
    
    ob_start();
    echo "<p>";
//     if (!strlen($_REQUEST["more"])) {
//         echo "<a href='?more=1&q=".urlencode($q)."'>Show all entries</a> | ";
//     }
//     echo "<a href='?sortby=rate_in&q=".urlencode($q)."'>Sort by least ratings</a> | ";
//     echo "<a href='?sortby=rate_out'>Sort by most coolness</a>";
//     echo "</p><p>";
    echo "<a href='?action=preview'>Browse Entries</a> | ";
    echo "<a href='?action=edit'>Edit Entry</a> | ";
    echo "<a href='?action=preview&uid={$params["uid"]}'>View Entry</a>";
    echo "</p>";
    $links = ob_get_contents();
    ob_end_clean();
    
    echo $links;
    
    echo "<form style='text-align:left;margin:10px;'>";
//     echo "<input type='hidden' name='action' value=''>";
    echo "<input type='hidden' name='sortby' value=\"".htmlentities($sortby)."\">";
    echo "<input type='text' name='q' value=\"".htmlentities($q)."\">";
    echo " <input type='submit' value='Search'>";
    echo "</form>";
    

    
    echo "<p><h3>Play another game!</h3></p>";
    
        echo "<p>Sort by: "; $qq = urlencode($q);
        echo "<a href='?sortby=default&q=$qq'>Default (both)</a> | ";
        echo "<a href='?sortby=ratings&q=$qq'>Least Ratings</a> | ";
        echo "<a href='?sortby=coolness&q=$qq'>Most Coolness</a>";
        echo "</p>";
        
        $_link="?action=preview";
        $r = array_slice($r_unrated,0,18+12,true);
        $myurl = get_bloginfo("url")."/wp-content/plugins/compo2";
        
        $cols = 6;
        $n = 0;
        $row = 0;
        echo "<table class='preview'>";
        foreach ($r as $e) {
            if (($n%$cols)==0) { echo "<tr>"; $row += 1; } $n += 1;
            $klass = "class='alt-".(1+(($row)%2))."'";
            echo "<td valign=bottom align=center $klass>";
            
            $link = "$_link&uid={$e["uid"]}";
            echo "<div><a href='$link'>";
            $shots = unserialize($e["shots"]);
            echo "<img src='".compo2_thumb($shots["shot0"],120,90)."'>";
            echo "<div class='title'><i>".htmlentities($e["title"])."</i></div>";
            $ue = unserialize($e["get_user"]);
            echo $ue["display_name"];
            echo "</a></div>";
//             if ($e["disabled"]) { echo "<div><i>disabled</i></div>"; }
//             else { if (!$e["active"]) { echo "<div><i>inactive</i></div>"; } }
            echo "<div style='color:#fff; margin: 3px; padding: 2px; background:#000; text-align:center; font-weight:bold;'>";
            echo htmlentities($params["{$e["etype"]}_title"]);
            echo "</div>";
            echo "<div style='font-size:10px;text-align:center;font-style:italic'><i>";
            
//             $img = "inone.gif";
//             $v = $e["rate_c"];
//             if ($v >= 50) { $img = "ibronze.gif"; }
//             if ($v >= 75) { $img = "isilver.gif"; }
//             if ($v >= 100) { $img = "igold.gif"; }
//             echo "<img align=left src='$myurl/images/$img' title='$v% Coolness' style='padding:0px;margin:0px;border:0px;'>";
            
            $rate_in = intval($e["rate_in"]);
            $rate_out = intval($e["rate_out"]);
            $rate_d = intval($e["rate_d"]);
            $legal_cats = intval($e["legal_cats"]);
            echo "(D:$rate_d=R:$rate_in-C:$rate_out)" . $legal_cats;
            echo "</div>";
            
        }
        echo "</table>";
        
    echo "<p style='font-size:8px;'>";
    echo "D = Default = R - C, except not quite that simple<br/>";
    echo "R = Ratings = how many ratings this Entry has received.<br/>";
    echo "C = Coolness = how many entries this user has rated<br/>";
    echo "L = Loser = someone who games the coolness ranking.  It's the honor system, people.  Everyone might think you are cool, but in your heart of hearts, you will know that you are a <i>loser</i>.</p>";
    
    $n = htmlentities(count($r_rated));
    echo "<p><h3>Previously rated entries ($n)</h3></p>";
    
//     echo "<p>Rate 25+ entries to earn a Coolness medal!</p>";
    $r = $r_rated;
    
    echo "<table>";
    echo "<tr><th><th><th>";
    foreach ($params["cats"] as $k) { echo "<th>".substr($k,0,3); }
    echo "<th>Txt";
    $myurl = get_bloginfo("url")."/wp-content/plugins/compo2";
    foreach ($r as $key=>$ce) {
        $ve = $r_rate[$ce["uid"]];
        $ue = unserialize($ce["get_user"]);
        echo "<tr>";
        echo "<td valign=center>";
        
/*        $img = "inone.gif";
        $v = $ce["rate_c"];
        if ($v >= 50) { $img = "ibronze.gif"; }
        if ($v >= 75) { $img = "isilver.gif"; }
        if ($v >= 100) { $img = "igold.gif"; }
        echo "<img src='$myurl/images/$img' title='$v% Coolness'>";*/
        
        echo "<td valign=center align=center>";
            $shots = unserialize($ce["shots"]);
            echo "<img src='".compo2_thumb($shots["shot0"],60,45)."' style='margin:5px;border:1px solid #000;'>";
            
        echo "<td valign=center>";
            echo "<a href='?action=preview&uid={$ce["uid"]}'>";
            echo "<div style='width:125px;height:20px;overflow:hidden;'><i>".htmlentities($ce["title"])."</i></div>";
            $name = $ue["display_name"];
            if (!strlen($name)) { $name = "?"; }
            echo htmlentities($name);
            if ($ce["rate_in"]) { echo " ({$ce["rate_in"]})"; }
            echo "</a>";
            
        $data = unserialize($ve["data"]);
        foreach ($params["cats"] as $k) {
            echo "<td align=center valign=center>".(strlen($data[$k])?intval($data[$k]):"-");
        }
        echo "<td align=center valign=center>".(strlen($ve["comments"])?"x":"-");
    }
    echo "</table>";

    
    
    echo $links;
}

function _compo2_rate_rate($params,$uid = "") {
    if (!$uid) { $uid = intval($_REQUEST["uid"]); }

    $ce = compo2_entry_load($params["cid"],$uid);
    
    echo "<p>";
    echo "<a href='?action=default'>Back to Rate Entries</a>";
    
    if ( current_user_can('edit_others_posts') ) {
		echo " | <strong><a href='?action=edit&uid=".$ce["uid"]."&admin=1'>ADMIN EDIT</a></strong>";
	}
	if ( get_current_user_id() === $uid ) {
		echo " | <strong><a href='?action=edit'>Edit Entry</a></strong>";
	}
	
	echo "</p>";

    if ($params["uid"] == $uid) {
        _compo2_preview_show($params,$uid,true);
        return;
    }
    
    $ce = compo2_entry_load($params["cid"],$uid);
    
    if (!$ce["is_judged"]) {
        _compo2_preview_show($params,$uid,true);
        return;
    }
    
    $div = $ce["etype"];
    
    $settings = unserialize($ce["settings"]);

    _compo2_preview_show($params,$uid,false);

    if (!$ce["id"]) { return; }

    $ve = array_pop(compo2_query("select * from c2_rate where cid = ? and to_uid = ? and from_uid = ?",array($params["cid"],$ce["uid"],$params["uid"])));
    $canvote = false;
    
    if ( ($params["uid"] != $uid) ) { 
    	if ( !isset($params["{$div}_judged"]) || ($params["{$div}_judged"] !== "0") ) {
	        echo "<h3>Rate this {$params['{$div}_title']} Entry</h3>";
	        
	        echo "<p><i>If you can't run this Entry, please leave a comment saying so and explaining why.  Do not score unrunnable entries.</i></p>";
	            
	        
	        $myurl = get_bloginfo("url")."/wp-content/plugins/compo2";
	        echo "<script type='text/javascript' src='$myurl/starry/prototype.lite.js'></script>";
	        echo "<script type='text/javascript' src='$myurl/starry/stars.js'></script>";
	        echo "<link rel='stylesheet' href='$myurl/starry/stars.css' type='text/css' />";
	    
	        echo "<form method=post action='?action=submit&uid=$uid'>";
	        echo "<p>";
	        if (isset($params["{$div}_cats"])) {
	            echo "<table>";
	            $data = unserialize($ve["data"]);
	            foreach ($params["{$div}_cats"] as $k) {
	            	if ( !isset($settings['OPTOUT'][$div][$k]) ) { 
		                echo "<tr><th>".htmlentities($k);
		                echo "<td>";
		                $v = intval($data[$k]);
		                echo "<script>new Starry('data[$k]',{name:'data[$k]',sprite:'$myurl/starry/newstars.gif',width:20,height:20,startAt:$v});</script>";
		        //         compo2_select("data[$k]",array(""=>"n/a","5"=>"5 - Best","4"=>"4","3"=>"3","2"=>"2","1"=>"1 - Worst"),$v);
		    		}
		    		$canvote = true;
	            }
	            echo "</table>";
	        } else {
	            echo "<i>This division does not have any voting categories.  Please leave comments for the author.</i>";
	        }
	        echo "</p>";
	        echo "<a name='vote'></a><h2>Comments (non-anonymous)</h2>";
	        $ve["comments"]="";
	        echo "<textarea name='comments' rows=4 cols=60>".htmlentities($ve["comments"])."</textarea>";
	        echo "<p><input type='submit' value='Submit'></p>";
	        echo "</form>";
	        
	        echo "<br/>";
	    }
    }
    _compo2_preview_comments($params,$uid,$form=(!$canvote));//true);
    _compo2_show_comments($params["cid"],$ce["uid"]);	// NOTE: Legacy comment system
    
    if ( $canvote ) {
        echo "<h2>NOTE: Use the comment box <a href='#vote'>OVER HERE</a>. Thanks!</h2>";
//        echo "<h2>Comments (non-anonymous)</h2>";
//        $ve["comments"]="";
//        echo "<textarea name='comments2' rows=4 cols=60>".htmlentities($ve["comments"])."</textarea>";
//        echo "<p><input type='submit' value='Submit'></p>";
    }
}

function _compo2_rate_submit($params) {
//     print_r($_REQUEST); die;
    $uid = intval($_REQUEST["uid"]);
    $ce = compo2_entry_load($params["cid"],$uid);
    
    if (!$ce["id"]) { compo2_error("Invalid Entry: uid=$uid"); }
    
    if ($uid == $params["uid"]) { compo2_error("You can't vote on your own Entry!"); }
    
    $data = array();
    $total = 0;
    foreach ($_REQUEST["data"] as $k=>$v) {
//         $data[$k] = strlen($v)?intval($v):""; // worked for old method
        $data[$k] = intval($v)?intval($v):""; // works for new javascript starry
        $total += $data[$k];
    }
    
    $comments = trim(compo2_strip($_REQUEST["comments"]));
//    $comments2 = trim(compo2_strip($_REQUEST["comments2"]));
//    if ( strlen($comments2) > 0 ) {
//   		$comments = $comments2;
//   	}
    
    $e=array(
            "cid"=>$params["cid"],
            "to_uid"=>$ce["uid"],
            "from_uid"=>$params["uid"],
            "data"=>serialize($data),
            "ts"=>date("Y-m-d H:i:s"),
        );
    $total += strlen($comments);
    if (strlen($comments)) {
        $user = compo2_get_user($params["uid"]);
        compo2_insert("c2_comments",array(
            "cid"=>$params["cid"],
            "to_uid"=>$uid,
            "from_uid"=>$params["uid"],
            "ts"=>date("Y-m-d H:i:s"),
            "content"=>$comments,
            "get_user"=>serialize(array(
                "display_name"=>$user->display_name,
                "user_nicename"=>$user->user_nicename,
                "user_email"=>$user->user_email,
            )),
        ));
    }
    $r = compo2_query("select * from c2_comments where cid = ? and to_uid = ? and from_uid = ?",array(
        "cid"=>$params["cid"],
        "to_uid"=>$uid,
        "from_uid"=>$params["uid"],
        ));
    $e["comments"] = intval(count($r)!=0);
    
    if ($total) {
        compo2_query("delete from c2_rate where cid = ? and to_uid = ? and from_uid = ?",array($params["cid"],$ce["uid"],$params["uid"]));
        compo2_insert("c2_rate",$e);
    }
    
    _compo2_rate_recalc($params,$ce["uid"]);
    _compo2_rate_io_calc($params,$ce["uid"]);
    _compo2_rate_io_calc($params,$params["uid"]);
    header("Location: ?action=default"); die;
}

function _compo2_rate_io_calc($params,$uid) {
    $cid = $params["cid"];
    $ce = compo2_entry_load($params["cid"],$uid);
 
//    $cc = array_pop(compo2_query("select count(*) cnt from c2_rate where cid = ? and to_uid = ?",array($cid,$uid)));
//    $in = $cc["cnt"];

//    echo "<!-- DAWG -->\n";
    $allcc = compo2_query("select data from c2_rate where cid = ? and to_uid = ?",array($cid,$uid));
    $all = array();
    foreach ($allcc as $ve) {
    	$data = unserialize($ve['data']);
    	$sum = 0;
    	foreach ($data as $part) {
    		$sum += intval($part);
    	}
    	if ( $sum > 0 ) {
    		$all[] = $data;
    	}
    }
//    echo "<!--\n";
//    print_r( $all );
//    echo "-->\n";
//    echo "<!-- New Total: " . count($all) . " vs Total: " . $in . " -->\n";
	$in = count($all);
	$in_all = count($allcc);


//    $cc = array_pop(compo2_query("select count(*) cnt from c2_rate where cid = ? and from_uid = ?",array($cid,$uid)));
//    $out = $cc["cnt"];

//    echo "<!-- RAWG -->\n";
    $allcc = compo2_query("select data from c2_rate where cid = ? and from_uid = ?",array($cid,$uid));
    $all = array();
    foreach ($allcc as $ve) {
    	$data = unserialize($ve['data']);
    	$sum = 0;
    	foreach ($data as $part) {
    		$sum += intval($part);
    	}
    	if ( $sum > 0 ) {
    		$all[] = $data;
    	}
    }
//    echo "<!--\n";
//    print_r( $all );
//    echo "-->\n";
//    echo "<!-- New Total: " . count($all) . " vs Total: " . $out . " -->\n";
	$out = count($all);
	$out_all = count($allcc);
	
	echo "<!-- In: " . $in . " (" . $in_all . ")  Out: " . $out . " (" . $out_all . ") -->\n";
    
    compo2_update("c2_entry",array(
        "id"=>$ce["id"],
        "rate_in"=>$in,
        "rate_out"=>$out,
    ));
}

function _compo2_rate_recalc($params,$uid) {
    $cid = $params["cid"];
    $ce = compo2_entry_load($params["cid"],$uid);
    $r = compo2_query("select * from c2_rate where cid = ? and to_uid = ?",array($cid,$uid));
    
    $data = array();
    foreach ($params["cats"] as $k) {
        $value = 0;
        $total = 0;
        $values = array();
        foreach ($r as $ve) {
            if ($ve["from_uid"] == $uid) { continue; } // no voting for self
            $dd = unserialize($ve["data"]);
            if (!strlen($dd[$k])) { continue; }
            $values[] = intval($dd[$k]);
        }
        sort($values);
        for($i=0;$i<$params["calc_droplow"];$i++) { array_shift($values); }
        for($i=0;$i<$params["calc_drophigh"];$i++) { array_pop($values); }
        foreach($values as $v) { $value += $v; $total += 1; }
        
        $data[$k] = ($total>=$params["calc_reqvote"]?round($value/$total,2):"");
    }
    compo2_update("c2_entry",array(
        "id"=>$ce["id"],
        "results"=>serialize($data),
    ));
}

?>