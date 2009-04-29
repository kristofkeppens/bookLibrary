<?php
  /**
   * 
   * Generate url for json request to get book editions
   * 
   * @return string generated url
   */
  
  function generateRequestUrlEditions() {
    
    $json_url = 'http://openlibrary.org/api/things?'
    			.'&query='.urlencode('{"type":"/type/edition", "isbn_'.$this->isbn_type.'":"'.$this->isbn.'"}');
    			
    return $json_url;
  }
  
  /**
   * generates url to retrieve the book item
   * 
   * @param $key
   * @return string encoded url
   */
  
  function generateRequestUrlItem($key) {
    $json_url = 'http://openlibrary.org/api/get?'
    			.'&key='.urlencode($key);
    			
    return $json_url;
  }
  
  /**
   * send request to open library, gets json content
   * and returns array with found editiins of that book.
   *  
   * @return array edions
   */
  function getBookEditions() {
    
    //initialize editions array
    $editions = array();
    
    //generate json request url
    $json_url = $this->generateRequestUrlEditions();
    
    //read json data
    if(ini_get('allow_url_fopen') == '1') {
      $ol_json = file_get_contents($json_url);
    } elseif (function_exists('curl_init') === true) {
      //initialize new curl resource
      $ch = curl_init();
      
      //set options
      curl_setopt($ch, CURLOPT_URL, $json_url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.6pre) Gecko/2009011606 Firefox/3.1');
      
      //get the content
      $ol_json = curl_exec($ch);
    }
    
   
    
    //generate json object
    $json_object = json_decode($ol_json);
    
    if($json_object->status != 'ok') {
      return array('error' => bookLibrary::ERROR_UNKNOWN_ISBN);
      
    }else {
      $editions = $json_object->result;
    }
    
    return $editions;
    
  }
  
  
  function getBookItem() {
    //initialize return value
    $item = array();
    
    //generate json request url
    $json_url = $this->generateRequestUrlItem($this->item_key);
    
    //read json data
    
  		if ( ini_get('allow_url_fopen') == '1' ) {
			
			$ol_json = file_get_contents($json_url);
			
		// or curl, if not and curl is enabled
		} elseif ( function_exists('curl_init') === true ) {
			
			// initialize new curl resource
			$ch = curl_init();
			
			// set options and use firefox user agent to mimic a browser
			curl_setopt($ch, CURLOPT_URL, $json_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.6pre) Gecko/2009011606 Firefox/3.1'); 
			
			// get the content
			$ol_json = curl_exec($ch);
		}
		
		//generate json object
		$json_object = json_decode($ol_json);
		
		if($json_object->status != 'ok') {
		  return array('error' => bookLibrary::ERROR_UNKNOWN_ISBN);
		} else{
		  $item = $json_object->result;
		}
		
		return $item;
		
  }

  function setISBN($isbn = null) {
    if(!is_null($isbn)) {
      switch(strlen($isbn)){
        
        case 10:
          $this->isbn = $isbn;
          $this->isbn_type = 10;
          break;
          
        case 13:
          $this->isbn = $isbn;
          $this->isbn_type = 13;
          break;
      }
    }
  }

  function setItemKey($key = null) {
    if(!is_null($key)) {
      $this->item_key = $key;
    }
  }
  
  function countProperties($object = null) {
    $counted_properties = 0;
    
    if( is_object($object)) {
      foreach ($object as $property) {
        $counted_properties++;
      }
    }
    
    return $counted_properties;
  }
  
  function getBook( $isbn ) {
    
    $isbn = $this->isbn;
    
    //error no ISBN number
    
    if ($isbn == 0) {
      $book_info = array('error' => bookLibrary::ERROR_UNKNOWN_ISBN);
    } else{
      
      //get book editions
      $editions = $this->getBookEditions();
      
      //error: unknown isbn
      if (array_key_exists('error', $editions) || count($editions) == 0) {
        $book_info = array('error' => bookLibrary::ERROR_UNKNOWN_ISBN);
      } else {
        $edition_count = count($editions);
        
        //get book items for the first three editions
        $book_editions = array();
        $book_property_counts = array();
        
        //set limit to max 3 items
        $item_limit = 3;
        
        if ($edition_count < 3) { $item_limit = $edition_count; }
        
        //get items from open library
        for( $i = 0; $i < $item_limit; $i++) {
          //set item key
          $this->setItemKey($editions[$i]);
          
          $book_item = $this->getBookItem();
          $property_count = $this->countProperties($book_item);
          
          $book_editions[$property_count] = $book_item;
          $book_property_counts[] = $property_count;
        }
        
        //sort item array by property count and take the best one
        rsort($book_property_counts);
        $book = $book_editions[$book_property_counts[0]];
        
        //Create book array
        $book_info['title']         = $book->title_prefix.$book->title;
  				$book_info['authors']       = $book->authors;
  				$book_info['publisher']     = $book->publishers[0];
  				$book_info['public_date']   = $book->publish_date;
  				$book_info['book_link']     = 'http://openlibrary.org'.$book->key;
  				$book_info['pages_total']   = $book->number_of_pages;
  				
  				$img_small  = array('URL' => 'http://covers.openlibrary.org/b/olid/'.str_replace('/b/', '', $book->key).'-S.jpg?default=http://wiki-beta.us.archive.org/static/images/blank.book.png');
  				$img_medium = array('URL' => 'http://covers.openlibrary.org/b/olid/'.str_replace('/b/', '', $book->key).'-M.jpg?default=http://wiki-beta.us.archive.org/static/images/blank.book.png');
  				$img_large  = array('URL' => 'http://covers.openlibrary.org/b/olid/'.str_replace('/b/', '', $book->key).'-L.jpg?default=http://wiki-beta.us.archive.org/static/images/blank.book.png');
  				
  				$book_info['images'] = array('small'  => $img_small,
  				                             'medium' => $img_medium,
  				                             'large'  => $img_large);
      }
    }
    
    return $book_info;
    
  }
