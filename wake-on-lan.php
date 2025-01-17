<?php
/*
 * PHPWOL - Send wake on lan magic packet from php.
 * PHP Version 5.6.28
 * @package PHPWOL
 * @see https://github.com/andishfr/wake-on-lan.php/ GitHub project
 * @author Andreas Schaefer <asc@schaefer-it.net>
 * @copyright 2017 Andreas Schaefer
 * @license https://github.com/AndiSHFR/wake-on-lan.php/blob/master/LICENSE MIT License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

 /**
  * Wake On Lan function.
  *
	* @param string      $mac         The mac address of the host to wake
	* @param string      $ip          The hostname or ip address of the host to wake
	* @param string      $cidr        The cidr of the subnet to send to the broadcast address
	* @param string      $port        The udp port to send the packet to
  *
	* @return bool|string             false  = No error occured, string = Error message
	*/
function wakeOnLan($mac, $ip, $cidr, $port, &$debugOut) {
	// Initialize the result. If FALSE then everything went ok.
	$wolResult = false;
	// Initialize the debug output return
	$debugOut = [];
	// Initialize the magic packet
	$magicPacket = str_repeat(chr(0xFF), 6);

	$debugOut[] = __LINE__ . " : wakeupOnLan('$mac', '$ip', '$cidr', '$port' );";

	// Test if socket support is available
	// if(!$wolResult && !extension_loaded('sockets')) {
	// 	$wolResult = 'Error: Extension <strong>php_sockets</strong> is not loaded! You need to enable it in <strong>php.ini</strong>';
	// 	$debugOut[] = __LINE__ . ' : ' . $wolResult;
	// }

	// Test if UDP datagramm support is avalable
	// if(!array_search('udp', stream_get_transports())) {
	// 	$wolResult = 'Error: Cannot send magic packet! Tranport UDP is not supported on this system.';
	// 	$debugOut[] = __LINE__ . ' : ' . $wolResult;
	// }

	// Validate the mac address
	if(!$wolResult) {
		$debug[] = __LINE__ . ' : Validating mac address: ' . $mac;
		$mac = str_replace('-',':',strtoupper($mac));
		if ((!preg_match("/([A-F0-9]{2}[:]){5}([0-9A-F]){2}/",$mac)) || (strlen($mac) != 17)) {
			$wolResult = 'Error: Invalid MAC-address: ' . $mac;
			$debugOut[] = __LINE__ . ' : ' . $wolResult;
		}
	}

	// Finish the magic packet
	// if(!$wolResult) {
	// 	$debugOut[] = __LINE__ . ' : Creating the magic paket';
	// 	$hwAddress = '';
	// 	foreach( explode('-', $mac) as $addressByte) {
	// 		$hwAddress .= chr(hexdec($addressByte));
	// 	}
	// 	$magicPacket .= str_repeat($hwAddress, 16);
	// }

	// Resolve the hostname if not an ip address
	if(!$wolResult && !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ) {
		$debugOut[] = __LINE__ . ' : Resolving host :' . $ip;
		$tmpIp = gethostbyname($ip);
		if($ip==$tmpIp) {
			$wolResult = 'Error: Cannot resolve hostname "' . $ip . '".';
			$debugOut[] = __LINE__ . ' : ' . $wolResult;
		} else {
			$ip = $tmpIp; // Use the ip address
		}
	}

	// If $cidr is not empty we will use the broadcast address rather than the supplied ip address
	// if(!$wolResult && '' != $cidr ) {
	// 	$debugOut[] = __LINE__ . ' : CIDR is set to ' . $cidr . '. Will use broadcast address.';
	// 	$cidr = intval($cidr);
	// 	if($cidr < 0 || $cidr > 32) {
	// 		$wolResult = 'Error: Invalid subnet size of ' . $cidr . '. CIDR must be between 0 and 32.';
	// 		$debugOut[] = __LINE__ . ' : ' . $wolResult;
	// 	} else {
	// 	  // Create the bitmask long from the cidr value
	// 		$netMask = -1 << (32 - (int)$cidr);
	// 		// Create the network address from the long of the ip and the network bitmask
	// 		$networkAddress = ip2long($ip) & $netMask;
 //      // Calulate the size fo the network (number of ip addresses in the subnet)
	// 		$networkSize = pow(2, (32 - $cidr));
	// 		// Calculate the broadcast address of the network by adding the network size to the network address
	// 		$broadcastAddress = $networkAddress + $networkSize - 1;

	// 		$debugOut[] = __LINE__ . ' : $netMask = ' . long2ip($netMask);
	// 		$debugOut[] = __LINE__ . ' : $networkAddress = ' . long2ip($networkAddress);
	// 		$debugOut[] = __LINE__ . ' : $networkSize = ' . $networkSize;
	// 		$debugOut[] = __LINE__ . ' : $broadcastAddress = ' . long2ip($broadcastAddress);

	// 		// Create the braodcast address from the long value and use this ip
	// 		$ip = long2ip($broadcastAddress);
	// 	}
	// }

	// Validate the udp port
	if(!$wolResult && '' != $port ) {
		$port = intval($port);
		if($port < 0 || $port > 65535 ) {
			$wolResult = 'Error: Invalid port value of ' . $port . '. Port must between 1 and 65535.';
			$debugOut[] = __LINE__ . ' : ' . $wolResult;
		}
	}

	// Can we work with wakeonlan?
	if(!$wolResult) {

		$debugOut[] = __LINE__ . " : Executing 'wakeonlan' shell command ($mac)";
		$output = `wakeonlan {$mac}`;
		// If $output is null then something went wrong. Otherwise a 'Sending ...'
		// meessage should be returned.
		if (null === $output) {
			$wolResult = 'Error: something went wrong :D';
			$debugOut[] = __LINE__ . ' : ' . $wolResult;
		} else {
			$debugOut[] = __LINE__ . 'wakeonlan responded with: ' . $output;
		}

	}

  if(!$wolResult) $debugOut[] = __LINE__ . ' : Done.';

  return $wolResult;
}

function endWithJsonResponse($responseData) {


	$jsonString = json_encode($responseData, JSON_PRETTY_PRINT);

	if(!$jsonString) {
		http_response_code(500);
		die('Internal Server Error! Cannot convert response to JSON.');
	}

	header('Content-Length: ' . strlen($jsonString) );
	header('Content-Type: application/json; charset=UTF-8');

	header('Last-Modified: ' . gmdate('D, d M Y H:i:s'));
  header('Cache-Control: no-cache, must-revalidate');
	die($jsonString);
}



// Init locale variables
$MESSAGE = false;     // false -> all is fine, string -> error message
$DEBUGINFO = [];         // Array of strings containing debug information


// Get the url parameters
$ENABLEDEBUG = isset($_GET['debug'])   ? $_GET['debug']   : false;
$OP          = isset($_GET['op'])      ? $_GET['op']      : '';
$MAC         = isset($_GET['mac'])     ? $_GET['mac']     : '';
$IP          = isset($_GET['ip'])      ? $_GET['ip']      : '';
$CIDR        = isset($_GET['cidr'])    ? $_GET['cidr']    : '';
$PORT        = isset($_GET['port'])    ? $_GET['port']    : '';
$COMMENT     = isset($_GET['comment']) ? $_GET['comment'] : '';


// Is it a "Get host state" request?
if('info'===$OP && '' != $IP) {

 $responseData = [ 'error' => false, 'isUp' => false ];

 $errStr = false;
 $errCode = 0;
 $waitTimeoutInSeconds = 3;

 exec("ping -W 1 -c 1 $IP", $errStr, $errCode);
 if(0 === $errCode) {
  	$responseData['isUp'] = true;
	} else {
	$responseData['isUp'] = false;
	$responseData['errCode'] = $errCode;
	$responseData['errStr'] = $errStr;
 }

 return endWithJsonResponse($responseData);
}

// Try to send the magic packet if at least a mac address and ip address was supplied
if('wol'===$OP && ''!==$MAC && '' != $IP) {

	$responseData = [ 'error' => false, 'data' => '' ];

	// Call to wake up the host
	$MESSAGE = wakeOnLan($MAC, $IP, $CIDR, $PORT, $DEBUGINFO);

	// If the request was with enabled debug mode then append the debug info to the response
	// To enable debug mode add "&debug=1" to the url
	if($ENABLEDEBUG) $responseData['DEBUG'] = $DEBUGINFO;

	// Keep the message or make it an empty string
  if(!$MESSAGE) {
		$responseData['data'] = 'Magic packet został wysłany do <strong>' . $MAC. '</strong>. Proszę czekać na uruchomienie hosta...';
	} else {
		$responseData['error'] = $MESSAGE;
	}
	return endWithJsonResponse($responseData);
}


// Try to send the magic packet if at least a mac address and ip address was supplied
if(''!==$MAC && '' != $IP) {
	// Call to wake up the host
	$MESSAGE = wakeOnLan($MAC, $IP, $CIDR, $PORT, $DEBUG);
  // Keep the message or make it an empty string
  if(!$MESSAGE) $MESSAGE = 'Magic packet został wysłany do <strong>' . $MAC. '</strong>. Proszę czekać na uruchomienie hosta...';
}

// Keep the message or make it an empty string
if(!$MESSAGE) $MESSAGE = '';

?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title data-lang-ckey="wake-on-lan">Wake On LAN</title>

    <link href="https://fonts.googleapis.com/css?family=Varela+Round" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <style>
      /*! Minimal styling here */
      body {
        font-family: 'Varela Round', 'Segoe UI', 'Trebuchet MS', sans-serif;
      }

			.ui-sortable tr {
				cursor:pointer;
			}

			.ui-sortable tr:hover {
				background:rgba(244,251,17,0.45);
			}

			.container-full {
				margin: 0 auto;
				width: 100%;
			}

	    .modal.modal-wide .modal-dialog { width: 80%; }
			.modal-wide .modal-body { overflow-y: auto; }

      .align-middle { vertical-align: middle !important; }

			.popover2{ display: block !important; max-width: 400px!important; width: auto; }

      footer { font-size: 80%; color: #aaa; }
      footer hr { margin-bottom: 5px; }

    </style>

  </head>
  <body>

  <!-- Container element for the page body -->
  <div class="container container-full">

		<div class="page-header">
			<h2><a href="https://github.com/AndiSHFR/wake-on-lan.php" data-lang-ckey="wake-on-lan">Wake On LAN</a></h2>
		</div>

  	<div class="row">
	  	<div class="col-xs-12 pageNotifications"></div>
  	</div>

		<div class="row">
			<div class="col-xs-12">
				<table id="items" class="table table-condensed xtable-bordered table-hover">
					<thead>
						<tr>
  						<th>&nbsp;</th>
							<th data-lang-ckey="mac-address">mac-address</th>
							<th data-lang-ckey="ip-or-host">ip-address</th>
							<th data-lang-ckey="cidr">subnet size (CIDR)</th>
							<th data-lang-ckey="port">port</th>
							<th data-lang-ckey="comment">comment</th>
						</tr>
					</thead>

					<tbody>
						<tr>
							<td><i class="glyphicon glyphicon-thumbs-down text-danger"></i></td>'
							<td>78:2b:cb:85:fe:8a</td>
							<td>192.168.10.105</td>
							<td>24</td>
							<td></td>
							<td>Kuchnia</td>
							<td><button class="btn btn-xs btn-block btn-warning wakeItem" type="button" data-lang-ckey="wakeup">Wake up!</button></td>
						</tr>
					</tbody>

					<tfoot>
					</tfoot>
				</table>
			</div>
		</div>

    <footer>
        <hr>
        <p class="pull-right">Copyright &copy; 2017 Andras Schaefer &lt;asc@schaefer-it.net&gt;</p>

       <p class="pull-left">
			  <!-- https://www.iconfinder.com/icons/32338/flag_spain_spanish_flag_icon#size=16 -->
        <a href="#" data-lang-switch="pl-PL"><img id="flag-pl" title="Polski" alt="Polski" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABVklEQVQ4T82TQUsCQRTH/2+0bVUIrCi8CLodPAm1uR76Bn2PPof4ZUpYpbQJgo4d0u3kpS6rUNAtiCJDF50XU0qCroKnHgPDvDfze7z3f0OYNQLAc/zaNRPTjolRqVSaPocwgHK5PEnA1O12HzOZTC709oKA53lVajabnWKxmF0F4LruFbVaLd9xHGsVQK1Wk/8A4LXbfiGfX60EKSXdVs78I3vf6g8CCC3zMiEZUMSImSYq9YakU1P4dsywBkQQpAEC0Ps8YwZY6QWTR2h8BZKqe7u+vRG3AhEB/QD0jIgQURTABFaMOBQuXt8lXTo7vr2VsAb8m1ksKEFprOazQpwY5y8fkm6OtzsHqUQ2UDr75HUYZTzBY0D9uSfp/mTz6TC3nsZw+qssAOgkuhcGwb3rXROSqXTSjKXXDGMUNSNhxf/1ZAj0hyPFQS/69qkelom2dMK/AYPPiB+zNnkBAAAAAElFTkSuQmCC"></img></a>
        <a href="#" data-lang-switch="de-DE"><img id="flag-de" title="Deutsch" alt="Deutsch" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABkUlEQVQ4jY3Ty27TQBSA4WOc2AE745mxnbEtobxCN8S5vGIfoizYFMTGkVjwAJUQm4oVrSOExIZ3qPN3EQcqtbkc6duc+XV2I/J8vBd257yJJyKvzuT9OzYajX6uVitmsxl1XbNYLI6q65q6rpnP53ie91F839/EcYxSCq01xpijtNYopYiiCM/z1jIMgtamKVmeM3GOsiwpnij3qoqiKHDOkec5xlp8329EwrCVNEWyHCkKpCz/q6rdzrlegUzcrrUpMhg08ncUtlgDLoPCQVWCm0CWgtWgDZg9DToBNYZxzNfAb+QmDFqsoUtTuszSWU1nTM/S2acMndF0iYI44sofNHIThC2JojMJnda70Bzw4gEZtkjEgyQ9zYPYA3RPgURcyaCRb5/Dll9jtvea7Z1he2dPMGzvE/gT8/7Sb+T7j7CFMZAABtCAPUD3TQLEfPgUNHJ7G24gBlQfnJL0bcz1ddDIZjP8Da+BsDc6Yd+9Yb32v4iIfSsyWU6nF8vp9N1ZqupiKWJWIuP02O88ax4BPEaWLPEaiXwAAAAASUVORK5CYII="></img></a>
        <a href="#" data-lang-switch="en-US"><img id="flag-en" title="English" alt="English" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACqklEQVQ4jY2Ta0iTARSGj1LURAVnWW3ewAxFy4Et5yxvoGQKZd7INDVn6TKvqdiypqGFpCIazghdidOo3ORLx8zmj1SwLdEmkvStUJCCknVBLfvx9kMhSDEfeDhw3sP5ceAQrcdqg95WMrIiIuvNlMvl1nK53HptdnWZd0TRTKS0DXm1vQhKa0ZJQx+EyY2Q3dXCL7EOVfeewylcjrnfWMe4+d1jcvLPMJ8oVMI1uhpxJUrsjZAjtUIFh9DryKzshm2wDHE1aih40XjtGIIRxzCMcIMxyQ1HMXGfkWdYDht6sRVROa04ltGI2IL7EKXWI+FKG4Rn65FcpoT76VoMtPdjediIBf0YFvSv8HPUhKbSawy5B11gD8XfQZS0BX7xtxEjVUCQUIuYSwr4J9YiOlcB3vFK6BQa/BgcxRfdCD4PjOLXywk0F8sY2uN/jj1T2gFemAzpsgfYF3oVmRUdcBAW4nxZG2z9LiNW9hD1tiIMc3yg2+ED3TZvDG8/iBLaxZBnSDbLFZchvVyJnYJ8SMrbQR4SSG90gNwyUFDdDeLE4+36G6JnYowhcjnFBqc0gPjpiEyrA+1OwcmcZpB9EpLyFSCbOESWtOMmeWOI+OgjPvqIBz3xUUQ2DDV19rKDb+agn/wArdEMvWkWWqMZQ6ZZ9BtZDE3NQW18j4/j0/huNMFinMJXgwkrJhYtVbcYelFZwy490sCiegJLZw8sXU9hUa33U5ca890azKs0mO9S41uPFo3ZeQwp9x9gJ4UiGIQiGAICYTjyHwMCYTgswnSAGFWurgzNLK+YN7jPllCPjTGki3KYhdQVSxJnLGbyV81yxqLkH7P+5ktZfCDXDYqj9loiDseF7LhiNy9fsYevQOwhEKzWjVzLeF6+YuLYBZGdneNm37kl/gDsSQH5dAvcewAAAABJRU5ErkJggg=="></img></a>
			 </p>

    </footer>

	</div>

		</div><!-- @end: .row -->

  </div><!-- @end: .container -->

  <!-- Script tags are placed at the end of the file to make html appearing faster -->
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"  crossorigin="anonymous"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js" integrity="sha256-KM512VNnjElC30ehFwehXjx1YCHPiQkOPmqnrWtpccM=" crossorigin="anonymous"></script>
	<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
  <script src="mini-i18n.js"></script>

<script>
$(function () { 'use strict'

	/*!
		* Construct a dummy object for the console to make console usage more easier.
		* In IE the console object is undefined when the developer tools are not open.
		* This leads to an exception when using console.log(...); w/o dev tools opened.
		*/
	console = (window.console = window.console || { log: function() {} });

  function pageNotify(msg, style, dismissable, autoClose) {
    if(!msg || '' === msg) { $('.pageNotifications').empty(); return };
    style = style || 'danger';
    dismissable = dismissable || false;
    autoClose = autoClose || 0;

    var $alert = $([
    '<div class="alert alert-' + style + ' alert-dismissable">',
    (dismissable ? '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>' : '' ),
    '<span>' + (msg || '') + '</span>',
    '</div>'
    ].join(''));

    $('.pageNotifications').append($alert);
    if(0 < autoClose) {
      $alert.fadeTo(autoClose, 500).slideUp(500, function(){
        $alert.slideUp(500);
      });
    }
  }

  var lastUpdateIndex = 1;
  function updateHostInfo() {
		var
		    $tr = $('#items tbody tr:nth-child(' + lastUpdateIndex + ')'),
			  $i = $tr.find('td:first-child >'),
			  item = {ip: $tr.children()[2].textContent,},
			  url= '?op=info&ip=' + item.ip,
			  rowsNo = $('#items tbody').children().length
				;

    // Now table row found then reset index to 0
    if(lastUpdateIndex = rowsNo) lastUpdateIndex = 1; else lastUpdateIndex++;

    // Make ajax request to get the state of the host
    $.ajax({
        url: url,
       type: 'GET',
       data: null,
       beforeSend: function(/* xhr */) {
				$i
				  .removeClass('glyphicon-thumbs-down glyphicon-thumbs-up text-danger text-success')
				  .addClass('glyphicon-eye-open text-muted')
					;
			  },
       success:  function(resp) {
          if('string' === typeof resp) { resp = { error: resp }; }
          if(resp && resp.error && resp.error !== '') {
						return pageNotify(resp.error, 'danger', true, 10000);
          }

				if(resp.isUp) {
					$i
				  .removeClass('glyphicon-eye-open text-muted')
				  .addClass('glyphicon-thumbs-up text-success')
					;
				} else {
					$i
				  .removeClass('glyphicon-eye-open text-muted')
				  .addClass('glyphicon-thumbs-down text-danger')
					;
				}

          // Reschedule update
          setTimeout(updateHostInfo, 5000);
        },
       error: function(jqXHR, textStatus, errorThrown ) {
  				pageNotify('Error ' + jqXHR.status + ' calling "GET ' + url + '":' + jqXHR.statusText, 'danger', true, 10000);
        },
       complete: function(result) {
  			}
      });

	}

	$.fn.miniI18n({
		debug: false,
    data: {
        'de-DE': {
					'wake-on-lan': 'Wake On LAN',
					'mac-address': 'MAC-Adresse',
					'ip-or-host': 'IP-Adresse oder Computername',
					'cidr': 'Subnetz Größe (CIDR)',
					'port': 'Port',
					'comment': 'Bemerkung',
					'export': 'Exportieren...',
					'import': 'Importieren...',
					'wakeup': 'Aufwecken!',
					'remove': 'Entfernen',
					'tpl-comment': 'Mein Notebook',
					'add': 'Hinzufügen'
        },
        'en-US': {
					'wake-on-lan': 'Wake On LAN',
					'mac-address': 'mac-address',
					'ip-or-host': 'ip-address or hostname',
					'cidr': 'subnet size (CIDR)',
					'port': 'port',
					'comment': 'Comment',
					'export': 'Export...',
					'import': 'Import...',
					'wakeup': 'Wake Up!',
					'remove': 'Remove',
					'tpl-comment': 'my notebook',
					'add': 'Add'
        },
        'pl-PL': {
					'wake-on-lan': 'Wake On LAN',
					'mac-address': 'Adres MAC',
					'ip-or-host': 'Adres IP lub nazwa hosta',
					'cidr': 'Rozmiar podsieci (CIDR)',
					'port': 'Port',
					'comment': 'Komentarz',
					'export': 'Eksportuj...',
					'import': 'Importuj...',
					'wakeup': 'Obudź!',
					'remove': 'Usuń',
					'tpl-comment': 'Mój laptop',
					'add': 'Dodaj'
        }
      }
  });

  $.fn.miniI18n('pl-PL');

	$('#items tbody').on('click', '.wakeItem', function(event) {
		event.preventDefault();

		var $tr = $(this).closest('tr'),
		    item = {
				mac: $tr.children()[1].textContent,
				ip: $tr.children()[2].textContent,
				cidr: $tr.children()[3].textContent,
				port: $tr.children()[4].textContent,
			},
			url= '?op=wol';

    // Make ajax request to get the state of the host
    $.ajax({
        url: url,
       type: 'GET',
       data: { op:'wol', mac: item.mac, ip: item.ip, cidr: item.cidr, port: item.port},
       beforeSend: function(/* xhr */) {
			  },
       success:  function(resp) {
          if('string' === typeof resp) { resp = { error: resp }; }
          if(resp && resp.error && resp.error !== '') {
						return pageNotify(resp.error, 'danger', true, 10000);
          }
					pageNotify(resp.data, 'success', true, 10000);
        },
       error: function(jqXHR, textStatus, errorThrown ) {
  				pageNotify('Error ' + jqXHR.status + ' calling "GET ' + url + '":' + jqXHR.statusText, 'danger', true, 10000);
        },
       complete: function(result) {
  			}
      });

	});


	  //Helper function to keep table row from collapsing when being sorted
		var fixHelperModified = function(e, tr) {
		var $originals = tr.children();
		var $helper = tr.clone();
		$helper.children().each(function(index) {
		  $(this).width($originals.eq(index).width())
		});
		return $helper;
  	};

  $("#items tbody").sortable({
    helper: fixHelperModified,
		stop: function(event,ui) { saveTableToLocalStorage(); }
	}).disableSelection();


  var STORAGE_ITEMNAME = 'wolItems', msg = '<?php echo $MESSAGE; ?>';

  if('' !== msg) pageNotify(msg, (msg.startsWith('Error') ? 'danger' : 'warning'), true, 10000);

  updateHostInfo();

});
</script>

  </body>
</html>
