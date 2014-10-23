<?php
// ?admin=1 will show results before the voting is closed for admin level users

function _compo_vote($m) {
    list($state,$opts) = explode(":",html_entity_decode($m[1]));
    $opts = explode(";",$opts);
    natcasesort($opts);
    $pid = intval($GLOBALS["post"]->ID);
    ob_start();
    
    $user = wp_get_current_user();
    if ($user->user_level >= 10) {
        if (isset($_REQUEST["admin"])) {
            $state = "closed";
        }
    }

    if ($state == "open") { _compo_vote_do($pid,$opts); }
    if ($state == "open") { _compo_vote_form($pid,$opts); }
    if ($state == "closed") { _compo_vote_results($pid); }
    $r = ob_get_contents();
    ob_end_clean();
    return $r;
}

function compo_vote_google($name) {
    $link = "http://www.google.com/search?q=".urlencode($name);
    return "<a href=\"$link\" target='_blank'>".htmlentities($name)."</a>";
}


function _compo_vote_results($pid) {
    // CACHE ///////////////////////////////////////////////////////////////
    if (($cres=compo2_cache_read(0,$ckey="compo_vote_results:$pid",5*60))!==false) {
        if (!isset($_REQUEST["admin"])) { echo $cres; return; }
    }
    ob_start();
    ////////////////////////////////////////////////////////////////////////

    global $compo;
    
    $r = compo_query("select * from {$compo["vote.table"]} where pid = ? and uid = ? order by value desc",array($pid,0));
    
    $r2 = compo_query("select count(*) c, name,value , concat(name,'|',value) as n_v from {$compo["vote.table"]} where pid = ? and uid != 0 group by n_v",array($pid));
    $data = array(); foreach ($r2 as $e) { $data[$e["name"]][$e["value"]] = $e["c"]; }
    
    echo "<table>";
    echo "<tr><th><th><th><th align=center>+1<th align=center>0<th align=center>-1";
    $n=1;
    foreach ($r as $e) {
        echo "<tr>";
        echo "<th>{$n}.";$n++;
        echo "<td>".compo_vote_google($e["name"]);
        $v = $e["value"];
        if ($v>0) { $v="+$v"; }
//         echo "<th>(".htmlentities($v).")";
        echo "<th>".htmlentities($v)."";
        echo "<td>".$data[$e["name"]]["1"];
        echo "<td>".$data[$e["name"]]["0"];
        echo "<td>".$data[$e["name"]]["-1"];
    }
    echo "</table>";
    
    // CACHE ///////////////////////////////////////////////////////////////
    $cres = ob_get_contents();
    ob_end_clean();
    compo2_cache_write(0,$ckey,$cres);
    echo $cres;
    ////////////////////////////////////////////////////////////////////////
}

function _compo_vote_do($pid,$opts) {
    global $compo;
    $cur = wp_get_current_user();
    $uid = $cur->ID;
    if (!$uid) { return; }
    
    $action = isset($_REQUEST["compo_vote_action"])?$_REQUEST["compo_vote_action"]:"";
    if ($action != "") {
        compo_query("delete from {$compo["vote.table"]} where pid = ? and uid = ?",array($pid,$uid));
        foreach ($opts as $k=>$name) {
            $key = "vote_{$k}";
            $v = (strlen($_REQUEST[$key])?max(-1,min(1,intval($_REQUEST[$key]))):"");
            if (strlen($v)) {
                compo_query("insert into {$compo["vote.table"]} (pid,uid,name,value) values (?,?,?,?)",array($pid,$uid,$name,$v));
            }
            compo_query("delete from {$compo["vote.table"]} where pid = ? and uid = ? and name = ?",array($pid,0,$name));
            $e = array_pop(compo_query("select sum(value) as v from {$compo["vote.table"]} where pid = ? and uid != 0 and name = ?",array($pid,$name)));
            compo_query("insert into {$compo["vote.table"]} (pid,uid,name,value) values (?,?,?,?)",array($pid,0,$name,$e["v"]));
        }
        echo "<p>Thanks for voting!</p>";
    }
}

function _compo_vote_form($pid,$opts) {
    global $compo;
    $cur = wp_get_current_user();
    $uid = $cur->ID;
    if (!$uid) { echo "<p>You must sign in to vote.</p>"; return; }
    
    $data = compo_query("select * from {$compo["vote.table"]} where pid = ? and uid = ?",array($pid,$uid));
    $r = array(); foreach ($data as $e) { $r[$e["name"]] = $e["value"]; }

    echo "<style>.s,.us { cursor:pointer; } .us { opacity: 0.15; -moz-opacity: 0.15; filter: alpha(opacity=15); }</style>";
//     echo "<p>$pid $uid</p>";

    $topurl = get_bloginfo("url");
    echo "<script type='text/javascript' src='$topurl/wp-content/plugins/compo/vote.js'></script>";

    echo "<form method='post'>";
    echo "<input type='submit' value='Vote!'>";
    
    echo "<input type='hidden' name='compo_vote_action' value=1>";
    echo "<table>";
    foreach ($opts as $k=>$name) {
        $key = "vote_{$k}";
        $v = $r[$name];
        echo "<tr>";
        echo "<td><nobr>";compo_vote_fakeajax($key,$v);echo "</nobr>";
        echo "<td align=left>".compo_vote_google($name);
    }
    echo "</table>";
    
//     $total = count($opts);
//     echo "<script type='text/javascript'>initvote($total);</script>";

    echo "<input type='submit' value='Vote!'>";
    echo "</form>";
}

function compo_vote_fakeajax($k,$v) {
    $topurl = get_bloginfo("url");
    echo "<input name='$k' id='$k' value='$v' onChange='view(\"$k\")' type='hidden'/>";
    $c = (strcmp($v,"1")==0?"s":"us");
    echo "<img src='$topurl/wp-content/plugins/compo/images/thumbsup.gif' id='1$k' alt='+1' class='$c' onClick='set(\"$k\",1)' />";
    $c = (strcmp($v,"0")==0?"s":"us");
    echo "<img src='$topurl/wp-content/plugins/compo/images/undecided.gif' id='0$k' alt='0' class='$c' onClick='set(\"$k\",0)' />";
    $c = (strcmp($v,"-1")==0?"s":"us");
    echo "<img src='$topurl/wp-content/plugins/compo/images/thumbsdown.gif' id='-1$k' alt='-1' class='$c' onClick='set(\"$k\",-1)' />";
}



function compo_vote($content) {
    $content = preg_replace_callback("/\[compo\-vote\:(.*?)\]/","_compo_vote",$content);
    return $content;
}

?>