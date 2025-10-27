<?php


class fidkweather  {

  /**
   * The decoded METAR is stored here.
   *
   * $decoded_metar is an array of arrays. Each sub-array corresponds
   * to a group of related weather-info. We have cloud-groups,
   * visibility-groups and so on.
   *
   * @var  string
   */
  var $taf;

 /**
   * The decoded TAF is stored here.
   *
   * @var  array
   */
  var $decoded_taf;

  /**
   * This constructor does nothing besides passing the input down the
   * hierarchy.
   *
   * @param	array	The initial properties of the object.
   */
   
   var $flight_condition;
   var $gusty = false;
   var $windy = false;

  function __construct($taf) {
    /* This class doesn't have any defaults, so it just calls the
     * parent constructor.
     */
    $this->taf=$taf;
    $this->decoded_taf = false;

  }


  /**
   * Helper-function used to store temperatures.
   *
   * Given a numerical temperature $temp in Celsius, coded to tenth of
   * degree, store in $temp_c, convert to Fahrenheit and store in
   * $temp_f.
   *
   * @param string   Temperature to convert, coded to tenth of
   * 		     degree, like 1015
   * @param   integer   Temperature measured in degrees Celsius
   * @param   integer   Temperature measured in degrees Fahrenheit
   * @access  private
   */
  function store_temp($temp, &$temp_c, &$temp_f) {
    /*
     * Note: $temp is converted to negative if $temp > 100.0 (See
     * Federal Meteorological Handbook for groups T, 1, 2 and 4).
     * For example, a temperature of 2.6�C and dew point of -1.5�C
     * would be reported in the body of the report as "03/M01" and the
     * TsnT'T'T'snT'dT'dT'd group as "T00261015").
     */

    if ($temp[0] == 1) {
      $temp[0] = '-';
    }
    $temp_c = number_format($temp,1);
    /* The temperature in Fahrenheit. */
    $temp_f = number_format($temp * (9/5) + 32, 1);
  }


  /**
   * Helper-function used to store speeds.
   *
   * $value is converted and stored based on $windunit.
   *
   * @param  float   The value one seeks to convert.
   * @param  string  The unit of $value.
   * @param  float &$knots   After $value has been converted into knots,
   *			     it will be stored in this variable.
   * @param  float &$meterspersec   After $value has been converted into
   *				    meters per second, it will be stored
   *				    in this variable.
   * @param  float &$milesperhour   After $value has been converted into
   *				    miles per hour, it will be stored
   *				    in this variable.
   * @access  private
   */
  function store_speed($value, $windunit, &$knots, &$meterspersec, &$milesperhour) {
    if ($value == 0) {
      $knots = 0;
      $meterspersec = 0;
      $milesperhour = 0;
      return;
    }

    if ($windunit == 'KT') {
      /* The windspeed measured in knots: */
      $knots        = number_format($value);
      /* The windspeed measured in meters per second, rounded to one
         decimal place  */
      $meterspersec = number_format($value * 0.5144, 1);
      /* The windspeed measured in miles per hour, rounded to one
         decimal place */
      $milesperhour = number_format($value * 1.1508, 1);
    } elseif ($windunit == 'MPS') {
      /* The windspeed measured in meters per second */
      $meterspersec = number_format($value);
      /* The windspeed measured in knots, rounded to one decimal
         place */
      $knots        = number_format($value / 0.5144, 1);
      /* The windspeed measured in miles per hour, rounded to one
         decimal place */
      $milesperhour = number_format($value / 0.5144 * 1.1508, 1);
    } elseif ($windunit == 'KMH') {
      /* The windspeed measured in kilometers per hour */
      $meterspersec = number_format($value * 1000 / 3600, 1);
      $knots        = number_format($value * 1000 / 3600 / 0.5144, 1);
      /* The windspeed measured in miles per hour, rounded to one
         decimal place */
      $milesperhour = number_format($knots * 1.1508, 1);
    }
  }


 /**
   * Decodes a raw TAF.
   *
   * This function loops over the various parts of the raw TAF, and
   * stores the different bits in $decoded_taf. It uses get_taf() to
   * retrieve the TAF, so it is not necessary to connect to the database
   * before you call this function.
   *
   * It should be noted that 3 arrays are filled.  Only one should be kept, we'll see that later.
   * periods1 contains the individual data for each period
   * periods2 contains accumulated data for each period, based on pervious periods
   * periods 3 contains hour-by-hour data, interpolated for BECMG type periods
   *
   * @return  array   The decoded TAF.
   * @see     $decoded_taf
   * @access  public
   */

  /*
   * taken from http://www.nws.noaa.gov/mdl/icwf/avnfps/editor.html#TAFDecoder
   * need to add NIL( AMD) (augmented data)
   * and time consistency checks, Consistency Checks Within a Group
   */

  /*
   * taken from http://www.srh.noaa.gov/ftproot/MSD/html/note7.html
   * need to add icing, turbulence and temp forecast
   *
   * FM change rapidly within less than one hour ->from begin to end
   * BECMG change slowly, normally within 2 hours, never exceed 4 hours -> interpolate each 30 min
   * TEMPO temporary, less than half of the period ->from 1/4 to 3/4 interpolating
   * PROB chance of occurence for the whole period ->from begin to end
   */

  /* todo:
   * include year,month,day in the 'time_from','time_to','time_set' values for easier searching and processing
  */

  function decode_taf() {

    /* initialization */
    $temp_visibility_miles = '';
    $vis_miles = 0;
    $decoded_taf = array();
    $decoded_taf['remarks'] = '';
    $decoded_taf['taf'] = $this->taf;
   
    //$decoded_taf['location'] = $this->get_location();

    if($this->taf!='') $parts = explode(' ', $this->taf);
    else {
      $parts = false;
    }
  
    if($parts === false) {
      $this->decoded_taf = false;
      return false;
    } else {
        $num_parts = count($parts);
    }

    $current_period = 0;
    $periods = array();
    $periods[0] = 'COMPLETE';

    $decoded_taf['icao'] = $parts[0];
    $tmp_time_use = $parts[2];
    $decoded_taf['time_emit'] = hm2YMDhm(substr($parts[1],2,4),$tmp_time_use);
    $decoded_taf['time_use_from'] = hm2YMDhm(substr($parts[2],2,2)."00",$tmp_time_use);
    $decoded_taf['time_use_to'] = hm2YMDhm(substr($parts[2],4,2)."00",$tmp_time_use);
    $periods[0] .= ' '.$parts[2];

    /* first pass to get remarks and periods */
    for ($i = 3; $i < $num_parts; $i++) {
      $part = $parts[$i];
      if($i<$num_parts-1) $part2 = $parts[$i+1];
      else $part2 = '';

      if ($part=='RMK' ||
   //   ereg('[T]+[A-Z0-9]*[/]+[A-Z0-9]*[Z]+',$part)) { //JF catch limited metwatch times as a remark
        preg_match('/[T]+[A-Z0-9]*[\/]+[A-Z0-9]*[Z]+/',$part)) { //JF catch limited metwatch times as a remark
        /* The rest of the TAF is either a remark or temporary
         * information. We keep the remark.
         */
	for($j=$i;$j<$num_parts; $j++)
	  $decoded_taf['remarks'] .= ' ' . $parts[$j];
 	$decoded_taf['remarks'] = trim($decoded_taf['remarks']);
	break;
      }
      else if ( (substr($part,0,2)=='FM') || $part=='BECMG' ||
		($part=='TEMPO') || (substr($part,0,4)=='PROB') ) {
	$current_period++;
	$periods[$current_period] = $part;
      }
      else {
	$periods[$current_period] .= ' '.$part;
      }

    }

    /* for each period, parse the data */
    if(count($periods)>0) {

      $decoded_periods = array();
      $decoded_periods2 = array();
       $decoded_periods3 = array();
      $full_period = $full_period2 = false;
      $last_fm_becmg = false;

      for($j=0;$j<count($periods);$j++) {
	$tmp_period = $periods[$j];
	$data_period = $tmp_period;
	$parts = explode(' ', $tmp_period);
	$num_parts = count($parts);

	$decoded_periods[$j] = array();
	$decoded_period = & $decoded_periods[$j];

	$first_i = 1;
	$time_from = $time_to = false;
	$type = $prob = false;
	$set_time_to = false;
	if ( (substr($parts[0],0,2)=='FM') ) {
	  $type = 'FM';
	  $time_from = substr($parts[0],2,4);
 	  $time_set = $time_from;
 	  $time_to = false;
	  $first_i = 1;
	  /* set the end time if the previous FM or BECMG to the start time of this one */
	  if($last_fm_becmg!==false && $decoded_periods[$last_fm_becmg]['time_to']===false) {
	    $set_time_to = $last_fm_becmg;
	  }
	  $last_fm_becmg = $j;
	}
	else if ($parts[0]=='BECMG' && isset($parts[1])) {
	  $type = 'BECMG';
	  $time_from = substr($parts[1],0,2).'00';
	  $time_set = substr($parts[1],2,2).'00';
 	  $time_to = false;
	  $first_i = 2;
	  /* set the end time if the previous FM or BECMG to the start time of this one */
	  if($last_fm_becmg!==false && $decoded_periods[$last_fm_becmg]['time_to']===false) {
	    $set_time_to = $last_fm_becmg;
	  }
	  $last_fm_becmg = $j;
	}
	else if ($parts[0]=='TEMPO' && isset($parts[1])) {
	  $type = 'TEMPO';
	  $time_from = substr($parts[1],0,2).'00';
	  $time_set = $time_from; //could interpolate for the first half ?
	  $time_to = substr($parts[1],2,2).'00';
	  $first_i = 2;
	}
	else if (substr($parts[0],0,4)=='PROB' && isset($parts[1])) {
	  $type = 'PROB';
	  $time_from = substr($parts[1],0,2).'00';
	  $time_set = $time_from;
	  $time_to = substr($parts[1],2,2).'00';
	  $prob = intval(substr($parts[0],4));
	  $first_i = 2;
	}
	else if ($parts[0]=='COMPLETE' && isset($parts[1])) {
	  $type = 'COMPLETE';
	  $data_period = substr($data_period,9);
	  $time_from = substr($parts[1],2,2).'00';
	  $time_set = $time_from;
	  $time_to = substr($parts[1],4,2).'00';
	  $first_i = 2;
	}
	else {
	  $first_i = $num_parts;
	}

	/* make timestamps */
   	$time_from = hm2YMDhm($time_from,$tmp_time_use);
   	$time_to = hm2YMDhm($time_to,$tmp_time_use,true);
   	$time_set = hm2YMDhm($time_set,$tmp_time_use);

	/* set the end time if the previous FM or BECMG to the start time of this one */
	if ($set_time_to!==false) {
	  $decoded_periods[$set_time_to]['time_to'] = $time_from;
	}

	/* put the basic info in the decoded_period */
	$decoded_period['data'] = $data_period;
	$decoded_period['type'] = $type;
	$decoded_period['time_from'] = $time_from;
	$decoded_period['time_to'] = $time_to;
	$decoded_period['time_set'] = $time_set;
	$decoded_period['prob'] = $prob;

	/* pass each element of the period */
	for($i=$first_i;$i<$num_parts;$i++) {
	  $part = $parts[$i];

	 // if (ereg('^([0-9]{3}|VRB)([0-9]{2,3})G?([0-9]{2,3})?(KT)', $part, $regs)) {
     if (preg_match('/^([0-9]{3}|VRB)([0-9]{2,3})G?([0-9]{2,3})?(KT)/', $part, $regs)) {
	    /* Wind Group */

 	    $decoded_period['desc']['wind']['deg'] = $regs[1];
	    $this->store_speed($regs[2],
 			       $regs[4],
 			       $decoded_period['desc']['wind']['knots'],
 			       $decoded_period['desc']['wind']['meters_per_second'],
 			       $decoded_period['desc']['wind']['miles_per_hour']);

	    if (!empty($regs[3])) {

	      /* We have a report with information about the gust.
	       * First we have the gust measured in knots.
	       */
 	      $this->store_speed($regs[3],
 				 $regs[4],
 				 $decoded_period['desc']['wind']['gust_knots'],
 				 $decoded_period['desc']['wind']['gust_meters_per_second'],
 				 $decoded_period['desc']['wind']['gust_miles_per_hour']);
	    }
	  //} elseif (ereg('^([0-9]{3})V([0-9]{3})$', $part, $regs) &&
      } elseif (preg_match('/^([0-9]{3})V([0-9]{3})$/', $part, $regs) &&
		    !empty($decoded_period['desc']['wind']['deg'])) {
     	    /*
	     * Variable wind-direction
	     */
	    $decoded_period['desc']['wind']['var_beg'] = $regs[1];
	    $decoded_period['desc']['wind']['var_end'] = $regs[2];
	  //} elseif (ereg('^([0-9]{4})([NS]?[EW]?)$', $part, $regs)) {
      } elseif (preg_match('/^([0-9]{4})([NS]?[EW]?)$/', $part, $regs)) {
	    /*
	     * Visibility in meters (4 digits only)
	     */
	    unset($group);

	    if ($regs[1] == '0000') {
	      /* Special low value */

	      $group['prefix'] = -1; /* Less than */
	      $group['meter']  = 50;
	      $group['km']     = 0.05;
	      $group['ft']     = 164;
	      $group['miles']  = 0.031;
	    } elseif ($regs[1] == '9999') {
	      /* Special high value */
	      $group['prefix'] = 1;
	      $group['meter']  = 10000;
	      $group['km']     = 10;
	      $group['ft']     = 32800;
	      $group['miles']  = 6.2;
	    } else {
	      /* Normal visibility, returned in both small and large units. */
	      $group['prefix'] = 0;
	      $group['km']     = number_format($regs[1]/1000, 1);
	      $group['miles']  = number_format($regs[1]/1609.344, 1);
	      $group['meter']  = $regs[1] * 1;
	      $group['ft']     = round($regs[1] * 3.28084);
	    }
	    if (!empty($regs[2])) {
	      $group['deg'] = $regs[2];
	    }
	    $decoded_period['desc']['visibility'][] = $group;

	  //} elseif (ereg('^[0-9]$', $part)) {
      } elseif (preg_match('/^[0-9]$/', $part)) {
	    /*
	     * Temp Visibility Group, single digit followed by space.
	     */
	    $temp_visibility_miles = $part;

	  } elseif ($part=='P6SM') {
	    unset($group);
	    $group['prefix'] = 1;
	    $vis_miles = 6;
	    $group['miles']  = $vis_miles;
	    $group['ft']     = round($vis_miles * 5280, 1);
	    $group['km']     = number_format($vis_miles * 1.6093, 1);
	    $group['meter']  = round($vis_miles * 1609.3);
	    $decoded_period['desc']['visibility'][] = $group;

	  //} elseif (ereg('^[M]?(([0-9]?)[ ]?([0-9])(/?)([0-9]*))SM$',
      } elseif (preg_match('/^[M]?(([0-9]?)[ ]?([0-9])(\/?)([0-9]*))SM$/',
			 $temp_visibility_miles . ' ' . $part, $regs)) {
	    /*
	     * Visibility Group
	     */
	    unset($group);

	    if ($regs[4] == '/') {
        if (is_numeric($regs[2]) && is_numeric($regs[3]) && is_numeric($regs[5]))
	      $vis_miles = $regs[2] + $regs[3]/$regs[5];
	    } else {
	      $vis_miles = $regs[1];
	    }
	    if ($regs[0][0] == 'M') {
	      /* Prefix - less than */
	      $group['prefix'] = -1;
	    } else {
	      $group['prefix'] = 0;
	    }

      //jf Format vis_miles
      if (!is_numeric($vis_miles)) {
        $vis_miles = 0;
      }
        /* The visibility measured in miles */
        $group['miles']  = number_format($vis_miles, 1);
        /* The visibility measured in feet */
        $group['ft']     = round($vis_miles * 5280, 1);
        /* The visibility measured in kilometers */
        $group['km']     = number_format($vis_miles * 1.6093, 1);
        /* The visibility measured in meters */
        $group['meter']  = round($vis_miles * 1609.3);

	    $decoded_period['desc']['visibility'][] = $group;
      
	  //} elseif (ereg('^(VC)?' .                           /* Proximity */
      } elseif (preg_match('/^(VC)?\/' .                           /* Proximity */
			 '(-|\+)?' .                          /* Intensity */
			 '(MI|PR|BC|DR|BL|SH|TS|FZ|NSW)?' .       /* Descriptor */
			 '((DZ|RA|SN|SG|IC|PL|GR|GS|UP)+)?' . /* Precipitation */
			 '(BR|FG|FU|VA|DU|SA|HZ|PY)?' .       /* Obscuration */
			// '(PO|SQ|FC|SS)?$',                   /* Other */
             '(PO|SQ|FC|SS)?$/',                   /* Other */
			 $part, $regs)) {
	    /*
	     * Current weather-group.
	     */
	    $decoded_period['desc']['weather'][] =
	      array('proximity'     => $regs[1],
		    'intensity'     => $regs[2],
		    'descriptor'    => $regs[3],
		    'precipitation' => $regs[4],
		    'obscuration'   => $regs[6],
		    'other'         => $regs[7]);

	  } elseif ($part == 'SKC' || $part == 'CLR') {
	    /* Cloud-group */
	    $decoded_period['desc']['clouds'][]['condition'] = $part;

	//  } elseif (ereg('^(VV|FEW|SCT|BKN|OVC)([0-9]{3}|///)' .
	//		 '(CB|TCU)?$', $part, $regs)) {
      } elseif (preg_match('/^(VV|FEW|SCT|BKN|OVC)([0-9]{3}|\/\/\/)' .
             '(CB|TCU)?$/', $part, $regs)) {
	    /* We have found (another) a cloud-layer-group. */
	    unset($group);

	    $group['condition'] = $regs[1];
	    if (!empty($regs[3])) {
	      $group['cumulus'] = $regs[3];
	    }
	    if ($regs[2] == '000') {
	      /* '000' is a special height. */
	      $group['ft']     = 100;
	      $group['meter']  = 30;
	      $group['prefix'] = -1; /* Less than */
	    } elseif ($regs[2] == '///') {
	      /* '///' means height nil */
	      $group['ft']     = 'nil';
	      $group['meter']  = 'nil';
	    } else {
	      $group['ft']     = $regs[2] *100;
	      $group['meter']  = round($regs[2] * 30.48);
	    }
	    $decoded_period['desc']['clouds'][] = $group;

	  //} elseif (ereg('^WS([0-9]{3})/([0-9]{3})([0-9]{2})KT$', $part, $regs)) {
      } elseif (preg_match('/^WS([0-9]{3})\/([0-9]{3})([0-9]{2})KT$/', $part, $regs)) {
	    /* We have found a Wind Shear group. example WS011/27050KT */
	    unset($ws);
	    if ($regs[1] == '000') {
	      /* '000' is a special height. */
	      $ws['ft']     = 100;
	      $ws['meter']  = 30;
	    } elseif ($regs[1] == '///') {
	      /* '///' means height nil */
	      $ws['ft']     = 'nil';
	      $ws['meter']  = 'nil';
	    } else {
	      $ws['ft']     = $regs[1] *100;
	      $ws['meter']  = round($regs[1] * 30.48);
	    }
 	    $ws['wind']['deg'] = $regs[2];
	    $this->store_speed($regs[3],'KT',
			       $ws['wind']['knots'],
			       $ws['wind']['meters_per_second'],
			       $ws['wind']['miles_per_hour']);
	    $decoded_period['desc']['wind_shear'][] = $ws;
	  }
	}
      }

      /* set the last FM or BECMG end time */
      if($last_fm_becmg!==false && $decoded_periods[$last_fm_becmg]['time_to']===false) {
 	$decoded_periods[$last_fm_becmg]['time_to'] = $decoded_periods[0]['time_to'];
      }

      $decoded_taf['periods1'] = $decoded_periods;
    }

    /* We pass each 'periods1' and set the properties for each based on previous properties.
     * We also construct the hour-by-hour report.  */
    if(count($decoded_taf['periods1'])>0) {
      $full_period = $decoded_taf['periods1'][0];
      for($j=0;$j<count($decoded_taf['periods1']);$j++) {
	$tmp_period = $decoded_taf['periods1'][$j];
 	$tmp_period2 = false;

	/* merge the full period with this one */
	if($tmp_period['type'] == "COMPLETE") $tmp_period2 = $full_period;
	else $tmp_period2 = $this->merge_period($tmp_period,$full_period);

	/* fill $decoded_periods3 with data from each hour */
	$tms_from = tms_date2unix($tmp_period2['time_from']);
	$tms_set = tms_date2unix($tmp_period2['time_set']);
	$tms_to = tms_date2unix($tmp_period2['time_to']);
	$duration = ($tms_to - $tms_from) / 60.0 / 60.0;
	for($k=0;$k<ceil($duration);$k++) {
 	  $tmp_period3 = $tmp_period2;
	  $tmp_tms_from = $tms_from + $k * 60*60;
	  $tmp_tms_to = $tmp_tms_from + 60*60;//MUST see if fraction...
	  $tmp_time_from = tms_unix2date($tmp_tms_from);
	  $tmp_time_to = tms_unix2date($tmp_tms_to);
	  $tmp_period3['time_from'] = $tmp_time_from;
	  $tmp_period3['time_to'] = $tmp_time_to;
 	  unset($tmp_period3['time_set']);
	  if($tmp_tms_from < $tms_set) {
	    $tmp_period3['interpolate']='yes';
	    $this->interpolate_period($tmp_period3,$full_period2,$tms_from,$tms_set,$tmp_tms_from,$tmp_tms_to);
	  }
	  /* add the forecast fot this hour */
 	  if ($tmp_period['type'] != "PROB") {
 	    $decoded_periods3[gmdate("H",$tmp_tms_from)] = $tmp_period3;
 	  }
	  else {
	    /* add a 'PROB' element to the [normal] forecast for this hour */
	    /* perhaps the PROB period should contain only the data that has changed
	     * and not the cumulative data, for simplicity  */
 	    $decoded_periods3[gmdate("H",$tmp_tms_from)]['PROB'] = $tmp_period3;
	  }
	}

	/* apply the permanent change for the next periods */
	if($tmp_period['type'] != "TEMPO" && $tmp_period['type'] != "PROB") {
	  $full_period2 = $full_period;
	  $full_period = $tmp_period2;
	}
 	$decoded_periods2[$j] = $tmp_period2;
      }
      $decoded_taf['periods2'] = $decoded_periods2;
      $decoded_taf['periods3'] = $decoded_periods3;
    }


    /* Finally we store our decoded TAF in $this->decoded_taf so
     * that other methods can use it.
     */

    $this->decoded_taf = $decoded_taf;
    return $decoded_taf;
  }

  /**
   * Gets the hour-by-hour decoded TAF.
   *
   * @return  array   an array of hour-by hour decoded TAF.
   * @access  public
   */
  function get_taf_at_time($time_from=false,$time_to=false) {
    if($this->decoded_taf===false) return false;
    if($time_from==false&&$time_to===false) return $this->decoded_taf['periods3'];
    $tmp_taf = array();
 //   while(list($i,$period) = each($this->decoded_taf['periods3'])) {
    foreach($this->decoded_taf['periods3'] as $i => $period)  {
      if( $period['time_from']<$time_to && $period['time_to']>$time_from) {
	$tmp_taf[$i] = $period;
      }
    }
    if(count($tmp_taf)==0) return false;
    else return $tmp_taf;
  }

  function merge_period(&$period1,&$period2) {
    $period = $period2;
    reset($period1);
    //while(list($key,$val) = each($period1)) {
    foreach($period1 as $key => $val){
      if(is_array($val)) {
	//while(list($key2,$val2) = each($val)) {
      foreach($val as $key2 => $val2) {
	      $period[$key][$key2] = $val2;
	}
      }
      else
	$period[$key] = $val;
    }
    return $period;
  }

  function interpolate_period(&$period,&$prev_period,$from,$set,$period_from,$period_to) {
    //only interpolate wind, not: -visibility -weather -clouds -ws
    $wind = $period['desc']['wind'];
    $prev_wind = $prev_period['desc']['wind'];
    $factor = ($period_to-$from)/($set-$from+60*60);

    $tmp_wind_speed1 = intval($prev_wind['knots']);
    $tmp_wind_speed2 = intval($wind['knots']);
    $tmp_wind_speed = $tmp_wind_speed1 + $factor*($tmp_wind_speed2-$tmp_wind_speed1);

    /* if VRB special treatment : set to VRB when on the VRB half */
    $tmp_wind_dir1 = $prev_wind['deg'];
    $tmp_wind_dir2 = $wind['deg'];
    if ($tmp_wind_speed1==0) $tmp_wind_dir = $tmp_wind_dir2;
    else if ($tmp_wind_speed2==0) $tmp_wind_dir = $tmp_wind_dir1;
    else {
      if( ($tmp_wind_dir1=='VRB' && $tmp_wind_dir1=='VRB') ||
	  ($factor<0.5 && $tmp_wind_dir1=='VRB') ||
	  ($factor>0.5 && $tmp_wind_dir2=='VRB') ) {
	      $tmp_wind_dir = 'VRB';
      }
      else {
        if ($tmp_wind_dir2 ==="VRB") $tmp_wind_dir2 = 0;
        if ($tmp_wind_dir1 ==="VRB") $tmp_wind_dir1 = 0;
        $tmp_wind_dir = $tmp_wind_dir1 + $factor*($tmp_wind_dir2-$tmp_wind_dir1);
      }
      
    }

    /* set the average values */
     $period['desc']['wind']['deg'] = $tmp_wind_dir;
    $this->store_speed($tmp_wind_speed,'KT',$period['desc']['wind']['knots'],
		       $period['desc']['wind']['meters_per_second'],
		       $period['desc']['wind']['miles_per_hour']);
  }

  function display_taf()  {
  //Display the taf in a pleasing manner
  //Check to see if there are TAFs
  if (empty($this -> decoded_taf["periods2"])) return;
    echo '<table align="left" width="100%" border="0" cellpadding="0" cellspacing="0">';
    foreach ($this->decoded_taf["periods2"] as $period) {
            echo "<tr><td>";
            $tafwarnings = $this->taf_warn ($period); //Set whether VFR, IFR, etc.
            //Color code flight conditions
             echo "<span class=\"$this->flight_condition\"> ";

              if ($this->windy) echo "<strong><i>";  //Put windy days in bold italics
              echo $period['data']."</i></strong></span><br>\n";  //print the dataand close formatting
             } // end of while list of TAF periods
          //Print remarks, too
          echo '<span class=\"VFR\"> ';
          echo $this->decoded_taf['remarks'].'</span>';
          echo "</td></tr>\n";
          echo "</table>";
    
          } // end of valid TAF

  function format_taf()  {
  //Format the taf in a pleasing manner and return it to the caller
  
    foreach ($this->decoded_taf["periods2"] as $period) {
              $answer = "";
              $answer .= $period['data']."<br>";  //print the dataand close formatting
             } // end of while list of TAF periods
          //Print remarks, too
          $answer .= $this->decoded_taf['remarks'];
          $answer .= "<br>";
          return($answer);
    
          } // end 
          
  function taf_warn ($period)  {
/*  Take a taf time period and return the following array
      FLIGHTCOND => VFR, MVFR, IFR, LIFR
      WINDWARN => boolean to indicate high winds, gusts, or variable direction*/            
        $tafinfo = $period['desc'];  
        
        // Find minimum visibility
        $minvis = 9999; //Unlimited
        foreach ($tafinfo['visibility'] as $tafvis) {
            if ($tafvis['miles'] < $minvis) $minvis = $tafvis['miles'];
        }

        // Find minimum ceiling
        $minft = 9999; //Unlimited
        foreach ($tafinfo['clouds'] as $tafclouds) {
            if  ($tafclouds['condition'] == "BKN" || $tafclouds['condition'] == "OVC")
               if ($tafclouds['ft'] < $minft)
                   $minft = $tafclouds['ft'];
        }

        //Find wind parms
        $windvelocity = 0;
        $tafwind = $tafinfo['wind'];
        $windvelocity = $tafwind['knots'];

        //Is it gusty?
        $gusty = false;
        if (!empty($tafwind['gust_knots']))
            if ($tafwind['gust_knots'] > 10)
               $gusty = true;
            if (! $gusty)
                if ($tafwind['deg'] == "VRB" & $windvelocity > 5)
                    $gusty = true;

      //Figure out all the flags
       if (($minvis < 1) || ($minft <= 500))      $this->flight_condition = 'LIFR';
       elseif (($minvis < 3) || ($minft <= 1000)) $this->flight_condition = 'IFR';
       elseif (($minvis < 5) || ($minft <= 3000)) $this->flight_condition = 'MVFR';
       else                                       $this->flight_condition = 'VFR';

       if ($gusty || $windvelocity > 20) $this->windy = true;
       else $this->windy = false;

       return;
 }

}

/* put these and other functions somewhere else? */

function hm2YMDhm($hm,$time_use,$next_day=false) {
  if($hm===false) return false;

  /* prepare the data */
  $Y = gmdate("Y");
  $M = intval(gmdate("n"));
  $d_time_use = intval(substr($time_use,0,2));
  $d = $d_time_use;
  $h_time_use = intval(substr($time_use,2,2));
  $h = intval(substr($hm,0,2));
  $m = intval(substr($hm,2,2));

  /* report is for next month, add a month */
  if($d > gmdate('j')) $M++;
  $tms = gmmktime($h,$m,0,$M,$d,$Y);
  /* report is for next day, add a day */
  if ($h<$h_time_use) $tms += 60*60*24;
  else if ($next_day===true && $h==$h_time_use) $tms += 60*60*24;

  $YMDhm = gmdate("YmdHi"."00",$tms);

  return $YMDhm;
}

function tms_date2unix($tmp_tms) {
  return gmmktime(substr($tmp_tms,8,2),substr($tmp_tms,10,2),substr($tmp_tms,12,2),
		substr($tmp_tms,4,2),substr($tmp_tms,6,2),substr($tmp_tms,0,4));
}

function tms_unix2date($tmp_tms) {
  return gmdate("YmdHis",$tmp_tms);
}

//Test code
//Get the raw taf
//$addsurl = "http://aviationweather.gov/adds/dataserver_current/httpparam?dataSource=tafs&requestType=retrieve&format=xml&stationString=KIAD&hoursBeforeNow=3";
//$xml = simplexml_load_file($addsurl); //Load up object with adds result
//$taf_rawtext = (string) $xml->data->TAF[0]->raw_text;
 
//$sometaf = new fidkweather($taf_rawtext);
//$sometaf->decode_taf();
//$sometaf->display_taf();
?>
