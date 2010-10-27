<?php
$scriptName = basename($_SERVER['SCRIPT_NAME']);
if ($scriptName == 'data.php') {
	define('TIMENOW',time() + 7*86400); // go ahead of time a little bit
	define('UPDATING_DATA_ONLY',1);
} else {
	// define('TIMENOW',time());
	define('TIMENOW',0); // never update upon normal request
}
function globalCache($data = false) {
	$cache = 'cache/global';
	if ($data === false) {
		// get cache
		if (file_exists($cache)) {
			return unserialize(file_get_contents($cache));
		} else {
			return array();
		}
	} else {
		file_put_contents($cache,serialize(array('updated' => time(),'data' => $data)));
	}
}

function my_file_get_contents($url,$doCache = true) {
	$cache = 'cache/' . md5($url);
	if (file_exists($cache)) {
		if ($doCache === true OR filemtime($cache) > TIMENOW - $doCache) {
			return file_get_contents($cache);
		}
	}
	
	$content = file_get_contents($url);
	file_put_contents($cache,$content);
	return $content;
}

function fb_get_contents($url,$prefix,$doCache = true) {
	$cache = 'cache/' . $prefix . md5($url);

	if (file_exists($cache)) {
		$content = unserialize(file_get_contents($cache));
	} else {
		$content = array();
	}
	
	if (empty($content) OR ($doCache === true AND empty($content)) OR filemtime($cache) > TIMENOW - $doCache) {
		$newContent = file_get_contents($url);
		$data = json_decode($newContent);
		foreach ($data->data as $entry) {
			$content[$entry->id] = $entry;
		}
		file_put_contents($cache,serialize($content));
		return $content;
	}
	
	return $content;
}

function printFeeds($feeds) {
	foreach ($feeds as $feed) {
		switch ($feed['type']) {
			case 'video': $type_str = 'B&#225;o h&#236;nh'; break;
			case 'photo': $type_str = 'B&#225;o &#7843;nh'; break;
			case 'text': $type_str = 'B&#225;o in'; break;
			case 'official': $type_str = 'Th&#244;ng c&#225;o'; break;
			default: $type_str = ''; break;
		}
		echo "\t";
		echo '<a href="' . $feed['original'] . '" target="baochihatisawesome" class="feed-' . $feed['type'] . '"';
		if (in_array($feed['type'],array('video','photo')) AND !empty($feed['title'])) echo ' title="' . $feed['title'] . '"';
		echo '>';
		if (!empty($feed['thumb'])) {
			if (is_array($feed['thumb'])) {
				echo '<img src="' . $feed['thumb'][0] . '" width="' . $feed['thumb'][1] . '" height="' . $feed['thumb'][2] . '" class="thumb" />';
			} else {
				echo '<img src="' . $feed['thumb'] . '" height="' . $GLOBALS['hfixed'] . '" class="thumb" />';
			}
		} else if (!empty($feed['title'])) {
			$theight = $GLOBALS['hfixed'];
			$twidth = $theight;
			switch ($feed['type']) {
				case 'official': $twidthLimit = 250; break;
				default: if (is_array($feed['author'])) $twidthLimit = 250; else $twidthLimit = 350;
			}
			if (strlen($feed['title']) > $twidthLimit) $twidth *= 1.5;
			echo '<div style="width: ' . $twidth . 'px; height: ' . $theight . 'px" class="feed-text-content thumb"><blockquote>';
			if (!empty($feed['author'])) {
				if (is_array($feed['author'])) {
					echo '<img src="' . $feed['author'][0] . '" title="' . $feed['author'][1] . '" class="avatar" />';
				} else {
					echo '<strong>' . $feed['author'] . '</strong>';
				}
			}
			echo "\t";
			echo nl2br(htmlspecialchars($feed['title']));
			echo '</blockquote></div>';
		} else {
			echo '&nbsp;';
		}
		echo '<span>' . $type_str . '</span>';
		echo '</a>';
		echo "\n";
	}
}

$hfixed = 125;
$perpage = 50;
$perpageAjax = ceil($perpage/3);
// $fbtoken = '115407971814270|921f66cf85522c2ec519464c-792241434|wYKnKdpZ201RQkUgk3z7-TUJBtk'; // sondh
$fbtoken = '115407971814270|8f0b19a82dfdfcebfd4f8d01-1498153953|120027548053941|g8tTDd8UY9RWQeE0rSqAtGXpBeM'; // baochihat, yuppiglet
$source = array(
	'facebook_profiles' => array(
		'BaochiHat',
	),
	'facebook_albums' => array(
		// will be added
	),
	'facebook_videos' => array(
		array(
			'vid' => '1314265276613',
			'thumb' => array('http://vthumb.ak.fbcdn.net/hvthumb-ak-ash2/hs642.ash2/50758_1314266276638_1314265276613_33763_673_t.jpg',160,90),
			'timeline' => 'Sat, 16 Oct 2010 03:13:38 -0700',
		),
		array(
			'vid' => '154913264546318',
			'thumb' => array('http://vthumb.ak.fbcdn.net/hvthumb-ak-snc4/hs1303.snc4/51012_154913991212912_154913264546318_29054_670_t.jpg',160,120),
			'timeline' => 'Mon, 18 Oct 2010 11:31:25 -0700',
		),
		array(
			'vid' => '164044320287175',
			'thumb' => array('http://vthumb.ak.fbcdn.net/hvthumb-ak-snc4/hs1423.snc4/51020_164044490287158_164044320287175_20989_488_t.jpg',160,120),
			'timeline' => 'Mon, 25 Oct 2010 01:30:26 -0700',
		),
	),
	'youtube' => array(
		'http://ponology.com/oauth/google_mrpaint_youtube_baochihat.php',
	),
);
$data = globalCache();
if (empty($data)) $data['data'] = array();
$feeds =& $data['data'];
if (empty($feeds) OR $data['updated'] < TIMENOW - 300) {
	// update data every 5 minutes
	set_time_limit(0);
	$oldFeeds = $feeds;
	$feeds = array();
	// Facebook's Profile
	foreach ($source['facebook_profiles'] AS $fbp) {
		// Facebook's Statuses
		// every 30 minutes
		$url = "https://graph.facebook.com/{$fbp}/statuses?fields=id,updated_time,message&access_token={$fbtoken}";
		foreach (fb_get_contents($url,'status_',1800) as $status) {
			$feeds['fb' . $status->id] = array(
				'original' => 'http://www.facebook.com/' . $fbp . '/posts/' . $status->id,
				'thumb' => false,
				'type' => 'official',
				'timeline' => strtotime($status->updated_time),
				'title' => $status->message,
			);
		}
		// Albums
		// every hour
		$url = "https://graph.facebook.com/{$fbp}/albums?limit=100&fields=id&access_token={$fbtoken}";
		$content = my_file_get_contents($url,3600);
		$data = json_decode($content);
		foreach ($data->data as $album) {
			$source['facebook_albums'][] = $album->id;
		}
		$source['facebook_albums'] = array_unique($source['facebook_albums']);
		// Facebook's Tagged Info
		// every 2 hour
		$url = "https://graph.facebook.com/{$fbp}/tagged?fields=id,type,created_time,link,picture,message,from&access_token={$fbtoken}";
		foreach (fb_get_contents($url,'tagged_',7200) as $tagged) {
			switch ($tagged->type) {
				case 'video':
					$source['facebook_videos'][] = array(
						'vid' => str_replace('http://www.facebook.com/video/video.php?v=','',$tagged->link),
						'thumb' => $tagged->picture,
						'timeline' => $tagged->created_time,
						'title' => @$tagged->message,
					);
					break;
				case 'photo':
					$feeds['fb' . $tagged->id] = array(
						'original' => str_replace('_s.jpg','_n.jpg',$tagged->picture),
						'thumb' => $tagged->picture,
						'type' => 'photo',
						'timeline' => strtotime($tagged->created_time),
						'title' => @$tagged->message,
					);
					break;
				case 'status':
					$feeds['fb' . $tagged->id] = array(
						'original' => 'http://www.facebook.com/' . str_replace('_','/posts/',$tagged->id),
						'thumb' => false,
						'type' => 'text',
						'timeline' => strtotime($tagged->created_time),
						'title' => $tagged->message,
						'author' => array('http://graph.facebook.com/' . $tagged->from->id . '/picture',$tagged->from->name),
					);
					break;
			}
		}
	}
	// Facebook's Albums (REAL)
	// every day
	foreach ($source['facebook_albums'] AS $fba) {
		$url = "https://graph.facebook.com/{$fba}/photos?limit=100&fields=id,images,name&access_token={$fbtoken}";
		$content = my_file_get_contents($url,86400);
		$data = json_decode($content);
		foreach ($data->data as $picture) {
			$original = false;
			$thumb = false;
			foreach ($picture->images as $image) {
				if ($image->height > 720 OR $image->width > 720) continue;
				if ($original === false) {
					$original = $image->source;
				}
				if ($image->height >= $hfixed/2) {
					$h = $hfixed;
					$w = ceil($h/$image->height*$image->width);
					$thumb = array($image->source,$w,$h);
				}
			}
			$feeds['fb' . $picture->id] = array(
				'original' => $original,
				'thumb' => $thumb,
				'type' => 'photo',
				'timeline' => strtotime($picture->created_time),
				'title' => @$picture->name,
			);
		}
	}
	// Facebook's Videos
	foreach ($source['facebook_videos'] AS $fbv) {
		$original = "http://www.facebook.com/v/$fbv[vid]";
		$thumb = $fbv['thumb'];
		if (is_array($thumb)) {
			$thumb[2] = $hfixed;
			$thumb[1] = ceil($thumb[2]/$fbv['thumb'][2]*$fbv['thumb'][1]);
		}
		$timeline = strtotime($fbv['timeline']);
		$feeds['fb' . $fbv['vid']] = array(
			'original' => $original,
			'thumb' => $thumb,
			'type' => 'video',
			'timeline' => $timeline,
			'title' => @$fbv['title'],
		);
	}
	// YouTube
	foreach ($source['youtube'] AS $yt) {
		$content = my_file_get_contents($yt,1800);
		$data = json_decode($content);
		foreach ($data as $video) {
			$vid = str_replace('http://gdata.youtube.com/feeds/api/videos/','',$video->id);
			$original = "http://www.youtube.com/v/$vid?fs=1";
			$ytthumb = $video->{'media:group'}->{'media:thumbnail'}->{'0_attr'};
			$thumb = array($ytthumb->url,ceil($hfixed/$ytthumb->height*$ytthumb->width),$hfixed);
			$timeline = strtotime($video->published);
			$feeds['yt' . $video->id] = array(
				'original' => $original,
				'thumb' => $thumb,
				'type' => 'video',
				'timeline' => $timeline,
				'title' => @$video->title,
			);
		}
	}

	usort($feeds,create_function('$a,$b','return $a["timeline"] < $b["timeline"];'));
	if (count($feeds) > count($oldFeeds) - 10) {
		globalCache($feeds);
	} else {
		$feeds = $oldFeeds;
	}
	
	if (defined('UPDATING_DATA_ONLY')) {
		echo 'Updated data. Number of entries: ' . count($feeds) . '<br/>';
		$counts = array();
		foreach ($feeds as $feed) {
			@$counts[$feed['type']]++;
		}
		foreach ($counts as $feedtype => $count) {
			echo 'Entries of type ' . $feedtype . ' = ' . $count . '<br/>';
		}
	}
}