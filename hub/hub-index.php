<?php ob_start(); ?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Ludum Dare - Home</title>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
<link rel="icon" href="/favicon.ico" type="image/x-icon" />
<STYLE TYPE="text/css">
<!--
BODY
{
font-family:arial,verdana,sans-serif;
}
-->
</STYLE>
</head>
<body bgcolor="#5f4f43" text="#ffffff" link="#f3bb11" alink="#ffffff" vlink="#f3bb11" TOPMARGIN="0" LEFTMARGIN="0" MARGINHEIGHT="0" MARGINWIDTH="0">
<?php
require_once 'hub/news.php';
?>

<table height="99%" width="100%" cellpadding="0" border="0">
	<td valign="middle" align="right" width="100%">
		<table cellspacing="0" cellpadding="0" border="0" width="100%">
			<td align="center">
				<table cellspacing="0" cellpadding="0" border="0" width="707">
					<tr>
						<td align="center" bgcolor="#90745e">
							<img src="hub/hub-header.png"/>
						</td>
					</tr>
				</table>

<?php /*
				<table cellspacing="0" cellpadding="0" border="0" width="707">
					<tr>
						<td align="left" valign="top" bgcolor="#90745e">
							<?php						
							echo "<img src='hub/hub-blank.png' width='17'>";
							?>
						</td>
						<td align="center" bgcolor="#90745e">
*/ ?>							
<?php /*
							<font size="+1"><b>Ludum Dare 16 Countdown</b> - <font color="#fbda81">
							<?php
							include 'compo/wp-content/themes/ludum/countdown.php';
							?>
							</font></font>
							<br />
							<br />
*/ ?>							
<?php /*

							<font size="+1">
							<?php
							$news = 'http://www.ludumdare.com/compo/author/news/';
							$newsfeed = 'http://www.ludumdare.com/compo/author/news/feed/';
							$newsitems = 3;
							$newsprefiximage = '';
							
							echo "<a href='$news'>";
							echo "<img src='hub/hub-headlines.png' border='0'>";
							echo "</a>";

							//echo "<a href='$newsfeed'>";
							//echo "<img src='hub/feed-icon-14x14.png' border='0'>";
							//echo "</a>";
							
							//echo "<img src='hub/hub-blank.png' width='14'>";
							
							//echo "<a href='$newsfeed'>";
							//echo "<img src='hub/hub-rss.png' border='0'>";
							//echo "</a>";							
							
							echo "<br />";

							show_news($newsfeed,$newsitems,$newsprefiximage,$newssuffiximage);
							
							echo "<br />";
							?>
							</font>
						</td>
						<td align="right" valign="bottom" bgcolor="#90745e">
							<?php
							echo "<a href='$newsfeed'>";
							echo "<img src='hub/feed-icon-14x14.png' border='0'>";
							echo "</a>";
							
							echo "<img src='hub/hub-blank.png' width='3' height='3'>";
							
							echo "<br />";
							
							echo "<img src='hub/hub-blank.png' width='3' height='3'>";
							?>
						</td>
					</tr>
				</table>
*/ ?>
				<table cellspacing="0" cellpadding="6" border="0" width="707">
					<tr>
						<td align="center" valign="top" bgcolor="#a4866a">
							<font size="-1">		
							<br />
							<a href="compo/"><img src="hub/hub-compo.png" border="0"></a><br />
							Home of the Ludum Dare game making competition<br />
							<br />
							<a href="planet/"><img src="hub/hub-planet.png" border="0"></a><br />
							Syndicated blogs from the Ludum Dare community<br />
							<br />
							<a href="http://twitter.com/ludumdare"><img src="hub/twitter.png" border="0"></a><br />
							Follow Ludum Dare on Twitter. Use hashtag #LD48<br />
							<br />
							</font>
						</td>
						<td align="center" valign="top" bgcolor="#a4866a">
							<font size="-1">	
							<br />
							<a href="compo/about-ludum-dare/"><img src="hub/hub-about.png" border="0"></a><br />
							What is Ludum Dare, and what is it about?<br />
							<br />
							<a href="http://www.gamecompo.com/mailing-list/"><img src="hub/hub-mailinglist.png" border="0"></a><br />
							The latest event news delivered to your mailbox<br />
							<br />
							<a href="compo/irc/"><img src="hub/hub-irc.png" border="0"></a><br />
							Join us live in #ludumdare on irc.afternet.org<br />
							<br />
							</font>
						</td>
					</tr>
				</table>
<?php /*

				<table cellspacing="0" cellpadding="16" border="0" width="707">
					<tr>
						<td bgcolor="#90745e">
							
						</td>
						<td align="left" valign="top" bgcolor="#90745e" width="50%">
							<font size="-1">
							<?php
							$news = 'http://www.ludumdare.com/compo/';
							$newsfeed = 'http://www.ludumdare.com/compo/feed/';
							$newsitems = 5;
							$newsprefiximage = '/hub/hub-dot.png';
							
							echo "<a href='$news'>";
							echo "<img src='hub/hub-latest.png' border='0'>";
							echo "</a>";
							
							echo "<img src='hub/hub-blank.png' width='14'>";
							
							echo "<a href='$newsfeed'>";
							echo "<img src='hub/hub-rss.png' border='0'>";
							echo "</a>";							
							
							echo "<br />";

							show_news($newsfeed,$newsitems,$newsprefiximage,$newssuffiximage);
							?>
							</font>
						</td>
						<td bgcolor="#90745e">
							
						</td>
						<td align="left" valign="top" bgcolor="#90745e" width="50%">
							<font size="-1">
							<?php
							$news = 'http://www.ludumdare.com/planet/';
							$newsfeed = 'http://www.ludumdare.com/planet/feed/';
							$newsitems = 5;
							$newsprefiximage = '/hub/hub-dot.png';
							
							echo "<a href='$news'>";
							echo "<img src='hub/hub-latestplanet.png' border='0'>";
							echo "</a>";
							
							echo "<img src='hub/hub-blank.png' width='14'>";
							
							echo "<a href='$newsfeed'>";
							echo "<img src='hub/hub-rss.png' border='0'>";
							echo "</a>";							
							
							echo "<br />";

							show_news($newsfeed,$newsitems,$newsprefiximage,$newssuffiximage);
							?>
							</font>
						</td>
						<td bgcolor="#90745e">
							
						</td>
					</tr>
				</table>
*/ ?>
				<table cellspacing="0" cellpadding="0" border="0" width="707">
					<tr>
						<td align="center" bgcolor="#90745e">
							<img src="hub/grad-bar.png"><br />
							<span style="text-decoration:none;font-weight:bold;color:#5f4f43;Font-size:12px">(c) 2002-20XX <a style="text-decoration:none" href="http://www.towlr.com">+</a> The Ludum Dare Community</span>
						</td>
					</tr>
				</table>
			</td>
		</table>
	</td>
</table>
</body>
</html>
