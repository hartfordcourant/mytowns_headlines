<?php 
	/*
	 *
	 */
	function getContentItem($slug){
		// access token
		$P2Paccesstoken = "xxx";
		// location
		$P2Purl = "http://content-api.p2p.tribuneinteractive.com/" . $slug; 
		// initialize curl
		$curl = curl_init();
		// xx
		$headr = array();
		$headr[] = 'Authorization: Bearer '. $P2Paccesstoken;
		// set curl options
		curl_setopt($curl, CURLOPT_URL, $P2Purl);
		//curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_HTTPHEADER,$headr);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
		// get the spreadsheet data using curl
		$json = curl_exec($curl);
		//echo $json;
		//xx
		$array = json_decode($json);
		// close the curl connection
		curl_close($curl);
		
		return $array;
	}
	function updateContentItem($slug, $body){
		/* p2p api access token */
		$P2Paccesstoken = '874ai9840kqvuyojkyqp4k49o6q56yyfa35';
		/* p2p api location of item to update */
		$P2Purl = 'http://content-api.p2p.tribuneinteractive.com/content_items/'.$slug.'.json';

		/* update body of array */
		$data = array( 'content_item' => array(
			'body' => $body
			)
		);
		$data_string = json_encode($data);

		/* Build the authentication array for CURLOPT_HTTPHEADER. */
		$headr = array();
		$headr[] = 'Authorization: Bearer ' . $P2Paccesstoken;
		$headr[] = 'Content-type: application/json';
		/* End authentication.  */

		/* Initiate cURL.  */
		$ch = curl_init($P2Purl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_HTTPHEADER,$headr);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
		 
		$response = curl_exec($ch);

		if((string)$response == ''){
			echo '<div id="update"><h1>Updated ' . $P2Pslug . '</h1>';
			date_default_timezone_set('EST');
			echo '<p>' . date('F j, Y, g:i a') . '</p></div>';
			echo $body;
		}
		else{
			echo 'Error updating' . $P2Pslug . 'please try again.';
		}
		/* End cURL call. */
	}
	/*
	 *
	 */
	function getStory($body,$slug,$headline,$display_time,$item_state){
		// xx
		$is_valid_town = false;
		// xx
		$is_two_words = false;
		// xx
		$story =array();
		// key words 
		$first_word = array('east','west','north','south','new','old');
		// key towns
		$towns = array('bristol','east hartford','enfield','glastonbury','hartford','manchester','middletown','simsbury','west hartford','wethersfield');
		// xx
		$first_word_body = explode(" ", $body[1]);
		// check if first word in story is the complete town name 
		foreach($towns as $value){
			if(strtolower($first_word_body[0]) == $value){
				$town_name = strtolower($first_word_body[0]);
				$is_valid_town = true;
			}
		}
		// check if town name is two words
		foreach($first_word as $value){
			if(strtolower($first_word_body[0]) == $value){
				$town_name = strtolower($first_word_body[0]) . " " . strtolower($first_word_body[1]);
				$is_valid_town = true;
				$is_two_words = true;
			}
		}
		if($is_two_words == true){
			// remove first three elements from array
			for($i=0;$i<3;$i++){
				unset($first_word_body[$i]);
			}
		}else{
			// remove first two elements from array
			for($i=0;$i<2;$i++){
				unset($first_word_body[$i]);
			}
		}
		// xx
		date_default_timezone_set('EST');
		$last_modified = date('g:i a', strtotime($display_time));
		// xx
		if($is_valid_town == true && $item_state == 'live'){
			$story[0] = $town_name;
			$story[1] = $slug;
			$story[2] = $headline;
			$story[3] = implode(" ",$first_word_body);
			$story[4] = $last_modified;
			return $story;
		}
	}
	// xx
	$result = getContentItem("current_collections/hc_community_news.json?include%5B%5D=items&limit=20");
	// xx
	$headlines = $result->collection_layout->items;
	// xx 
	$stories = array();
	// xx
	$body = array();
	// xx
	$NUM_STORIES = 3;
	// xx
	foreach ($headlines as $value) {
		// xx
		$display_time = $value->display_time;
		// xx
		$item_state = $value->content_item_state_code;
		// xx
		$item = getContentItem("content_items/" . $value->slug . ".json");
	    // xx
	    $body = explode("<p>", str_replace("</p>", "", $item->content_item->body));
	    // xx
		$story = getStory($body,$value->slug,$value->headline,$display_time,$item_state);
		// xx
		if($story != null){
			array_push($stories, $story);
		}
		//var_dump($value);

	}
	// xx
	$mytowns = "";
	$mytowns .= '<link href="http://hc-assets.s3.amazonaws.com/css/barkers.css" rel="stylesheet" type="text/css" />';
	$mytowns .= '<link href="http://hc-assets.s3.amazonaws.com/css/mytowns-headlines.css" rel="stylesheet" type="text/css" />';
	$mytowns .= '<div class="group" id="barker_container">';
	$mytowns .= '<div id="hed" class="clearfix"><img src="http://www.trbimg.com/img-5410cad8/turbine/hc-my-towns-logo-sm-white" style="height:30px;" alt="Courant Town News" />';
	$mytowns .= '<form><select class="form-control" id="dropdown" onchange="onChange(this);"><option>Find your town page</option><runtime:include slug="hc-town-selector"/></select></form></div>';
	$mytowns .= '<div id="barker" class="clearfix">';
	$mytowns .= '<div id="story_group" class="clearfix">';
	//get headlines
	for($i=0;$i<$NUM_STORIES;$i++){
		$mytowns .= '<div class="story">';
		$mytowns .= '<p class="label">' . $stories[$i][0] . '<span class="label_time">' . $stories[$i][4] . '</span></p>';
		$mytowns .= '<h2><a href="http://www.courant.com/community/' . $stories[$i][1] . '-story.html" target="_parent">' . $stories[$i][2] . '</a></h2>';
	    $mytowns .= '</div>'; 
	}
	$mytowns .= '</div></div></div>';
	$mytowns .= '<script type="text/javascript">function onChange(option){var e = document.getElementById("dropdown");var slug = e.options[e.selectedIndex].value;if (!slug) { return false; }window.top.location.href = "/community/" + slug;};</script>';

	//xx
	updateContentItem('hc-mytowns-headlines-barker', $mytowns);
?>
