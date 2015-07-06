<?php
	/*
	    Copyright ©2015 Grant Sewell <grantsewell{at}gmail{dot}com>

	    This program is free software: you can redistribute it and/or modify
	    it under the terms of the GNU General Public License as published by
	    the Free Software Foundation, either version 3 of the License.

	    This program is distributed in the hope that it will be useful,
	    but WITHOUT ANY WARRANTY; without even the implied warranty of
	    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	    GNU General Public License for more details.

	    You should have received a copy of the GNU General Public License
	    along with this program.  If not, see <http://www.gnu.org/licenses/>.
	*/
	error_reporting('E_NONE');

	function show_help(){
		echo "\nBlue Coat Site Review Bulk Category Checker\n";
		echo "Copyright ©2015 Grant Sewell <grantsewell{at}gmail{dot}com>\n\n";
		echo "This program is free software: you can redistribute it and/or modify\n";
		echo "it under the terms of the GNU General Public License as published by\n";
		echo "the Free Software Foundation, either version 3 of the License.\n\n";

		echo "This program is distributed in the hope that it will be useful,\n";
		echo "but WITHOUT ANY WARRANTY; without even the implied warranty of\n";
		echo "MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the\n";
		echo "GNU General Public License for more details.\n\n";

		echo "You should have received a copy of the GNU General Public License\n";
		echo "along with this program.  If not, see <http://www.gnu.org/licenses/>.\n\n";

		echo "Usage:\n";
		echo "php bluecoat_sitereview.php [param]\n\n";

		echo "Example:\n";
		echo "php bluecoat_sitereview.php -u google.com -t 15 -v\n\n";

		echo "Parameters:\n\n";

		echo "-h, -help		Show this help message\n";
		echo "-u, -url		URL (i.e. google.com, http://google.com, etc.)\n";
		echo "-t, -timeout	Set cURL timeout for connection (default is 10s)\n";
		echo "-f, -file 		Set the filename to export to (myfile.txt)\n";
		echo "-v, -verbose	Verbose output (cURL Debug Mode)\n";
		exit(0);
	}

	// Function to do an individual URL search
	function get_category($strURL,$curl_timeout,$debug=false) {

		// This is the URL for Blue Coat's Site Review web page - Valid 7/2015
		$check_url  = "http://sitereview.bluecoat.com/rest/categorization";

		// Trim off anything we don't need
		$strURL = trim($strURL);

		// This array is used to post the URL
		$fields_string = "url=". $strURL;
		
		// Setup cURL
		$ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $check_url);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; en-US)');
	    curl_setopt($ch, CURLOPT_REFERER, 'http://www.sitereview.bluecoat.com/siterevew.jsp');
	    curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $curl_timeout);
		
		// Debugging
		if ($debug != false){
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		}
		
		// Execute the cURL setup
	    $curl_html_output = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    curl_close ($ch);

		// Error Handling
		if ($httpCode != 200){
			echo "Error (cURL Response code: ".$httpCode.")\n";
			exit(3);
		}
		
	    // Find the category HTML tag
		if (preg_match("/<a href.*catnum.*\">.*<\/a>/", $curl_html_output, $matches)) {
			
			// If there's two categories, break it apart
			$category = explode(" and ", strip_tags($matches[0]));

			// Print the results
			echo "\n****************\n";
			echo "CATEGORY RESULTS\n";
			echo "****************\n\n";
			echo "The URL you requested was:\t" . $strURL . "\n";
			echo "The Primary Category is:\t" . $category[0];
			if (isset($category[1])) { echo "\nThe Secondary Category is:\t" . $category[1] . "\n";}
			echo "\n";
			exit(0);
		} else {
			// Something went wrong. Parse the error and evaluate.
			preg_match("/errorType.*}/", $curl_html_output, $matches);
			$curlrep = str_replace('"', "", $matches[0]);
			$curlrep = str_replace('}', "", $curlrep);
			$curlerror = explode(":", $curlrep);
			if ($curlerror[1] == "intrusion") {
				echo "Blue Coat has flagged your IP address for too many requests.\nTry again in about 15 minutes.\n";
			} else if ($curlerror[1] == "captcha") {
				echo "Blue Coat is requiring a CAPTCHA to be entered.\nTry again in about 2 minutes.\n";
			}
			exit(0);
		}

	}

	// Function to do a bulk URL search
	function get_category_bulk($strBulk,$curl_timeout,$debug=false) {

		$try = 0;

		// This is the URL for Blue Coat's Site Review web page - Valid 7/2015
		$check_url  = "http://sitereview.bluecoat.com/rest/categorization";

		// Load the file into an array
		$bulklist = file($strBulk);
		echo "Starting bulk file processing...\n\n";
		$numURLs = count($bulklist);
		$seconds = $numURLs * 15;
		$hours = floor($seconds / 3600);
		$mins = floor(($seconds - ($hours*3600)) / 60);
		$secs = floor($seconds % 60);

		echo count($bulklist) . " URLs to check.\nEstimated time to completion... ";
		printf("%02d", $hours);
		echo ":";
		printf("%02d", $mins);
		echo ":";
		printf("%02d", $secs);
		echo "\n\n";

		foreach ($bulklist as $line_num => $strURL) {

			$linecount = $line_num + 1;
			// Trim off anything we don't need
			$strURL = trim($strURL);

			// This array is used to post the URL
			$fields_string = "url=". $strURL;
			
			// Setup cURL
			$ch = curl_init();
		    curl_setopt($ch, CURLOPT_URL, $check_url);
		    curl_setopt($ch, CURLOPT_HEADER, 0);
		    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; en-US)');
		    curl_setopt($ch, CURLOPT_REFERER, 'http://www.sitereview.bluecoat.com/siterevew.jsp');
		    curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, $curl_timeout);
			
			// Debugging
			if ($debug != false){
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			}
			
			start:
			// Execute the cURL setup
			echo "(" . $linecount . "/" . count($bulklist) . ") Processing " . $strURL;
		    $curl_html_output = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		    curl_close($ch);

			// Error Handling
			if ($httpCode != 200){
				echo "...ERROR! (cURL Response code: ".$httpCode.") ";
				// Try again 3 times, otherwise move forward and log the error (below)
				if ($try < 3) {
					echo "Trying again...\n";
					$try = $try + 1;
					goto start;
				}
			}

			// Wait 20 seconds between each URL
			sleep(2);
			echo ".";
			sleep(1);
			echo ".";
			sleep(2);
			echo ".";
			sleep(1);
			echo ".";
			sleep(2);
			echo ".";
			sleep(1);
			echo ".";
			sleep(2);
			echo ".";
			sleep(1);
			echo ".";
			sleep(2);
			echo ".";
			sleep(1);
			echo ".";
			sleep(1);
			echo ".";
			sleep(1);
			echo ".";
			sleep(1);
			echo ".";
			sleep(1);
			echo ".";
			sleep(1);
			echo ".";
			sleep(1);
			
		    // Find the category HTML tag
			if (preg_match("/<a href.*catnum.*\">.*<\/a>/", $curl_html_output, $matches)) {
				
				// If there's two categories, break it apart
				$category = explode(" and ", strip_tags($matches[0]));

				// Export to a text file
				$file = 'export.txt';
				// Open the file to get existing content
				$current = file_get_contents($file);
				if ($linecount == 1) {
					// Setup the headers
					$current .= "URL\tCategory [1]\tCategory[2]\n";
				}
				// Append the URLs
				$current .= $strURL . "\t" . $category[0] . "\t" . $category[1] . "\n";
				// Write the contents back to the file
				file_put_contents($file, $current);

				// Export to HTML too
				$file = 'export.htm';
				// Open the file to get existing content
				$current = file_get_contents($file);
				if ($linecount == 1) {
					// Setup the headers
					$current .= "<html><head><title>Bulk Category Check</title></head><body>";
					$current .= "<h1>Bulk Category Check</h1>\n";
					$current .= "<table border=1><tr><td>URL</td><td>Category [1]</td><td>Category[2]</td>\n";
				}
				// Append the URLs
				$current .= "<tr><td>" . $strURL . "</td><td>" . $category[0] . "</td><td>" . $category[1] . "</td>\n";
				//Add the footer
				if ($linecount == count($bulklist)) {
					$current .= "</table></body></html>";
				}
				// Write the contents back to the file
				file_put_contents($file, $current);

				echo "done.\n";

				if (substr($linecount, -1) == 0 && ($numURLs - $linecount > 4)) {
					$seconds = ($numURLs - $linecount) * 15;
					$hours = floor($seconds / 3600);
					$mins = floor(($seconds - ($hours*3600)) / 60);
					$secs = floor($seconds % 60);
					echo "\n\nEstimated time to completion... ";
					printf("%02d", $hours);
					echo ":";
					printf("%02d", $mins);
					echo ":";
					printf("%02d", $secs);
					echo "\n\n";
				}
			} else {
				// Something went wrong. Parse the error and evaluate.
				echo "ERROR!\n";
				preg_match("/errorType.*}/", $curl_html_output, $matches);
				$curlrep = str_replace('"', "", $matches[0]);
				$curlrep = str_replace('}', "", $curlrep);
				$curlerror = explode(":", $curlrep);
				if ($curlerror[1] == "intrusion") {
					echo "Blue Coat has flagged your IP address for too many requests.\nThe script will pause for 15 minutes and try again.\n";
					sleep(900);
					goto start;
				} else if ($curlerror[1] == "captcha") {
					echo "Blue Coat is requiring a CAPTCHA to be entered.\nThe script will pause for 2 minutes and try again.\n";
					sleep(120);
					goto start;
				} else {
					// Export to a text file
					$file = 'export.txt';
					// Open the file to get existing content
					$current = file_get_contents($file);
					if ($linecount == 1) {
						// Setup the headers
						$current .= "URL\tCategory [1]\tCategory[2]\n";
					}
					// Append the URLs
					$current .= $strURL . "\tERROR\t\n";
					// Write the contents back to the file
					file_put_contents($file, $current);

					// Export to HTML too
					$file = 'export.htm';
					// Open the file to get existing content
					$current = file_get_contents($file);
					if ($linecount == 1) {
						// Setup the headers
						$current .= "<html><head><title>Bulk Category Check</title></head><body>";
						$current .= "<h1>Bulk Category Check</h1>\n";
						$current .= "<table border=1><tr><td>URL</td><td>Category [1]</td><td>Category[2]</td>\n";
					}
					// Append the URLs
					$current .= "<tr><td>" . $strURL . "</td><td>ERROR</td><td></td>\n";
					//Add the footer
					if ($linecount == count($bulklist)) {
						$current .= "</table></body></html>";
					}
					// Write the contents back to the file
					file_put_contents($file, $current);
				}
			}
		}
		echo "\nFile Processing Complete.\n\n";
		exit(0);
	}

	// Parse command line arguments
	for($i=0;$i<count($argv);$i++)
	{
		// If nothing is set, show the help documentation
		if (!isset($argv[1])){ show_help();}

		// If help (-h, -help) is set
		if ($argv[$i] == "-h" OR $argv[$i] == "-help") { show_help();}
	  
		// If URL (-u, -url) 
		elseif ($argv[$i] == "-u" OR $argv[$i] == "-url" AND isset($argv[$i+1])) { $strURL = $argv[$i+1];}

		// If Bulk (-b, -bulk) 
		elseif ($argv[$i] == "-b" OR $argv[$i] == "-bulk" AND isset($argv[$i+1])) { $strBulk = $argv[$i+1];}
	    
		// If Timeout (-t, -timeout)
	    elseif ($argv[$i] == "-t" OR $argv[$i] == "-timeout" AND isset($argv[$i+1])) { $curl_timeout=$argv[$i+1];}

	    // If File Export (-f, -file)
	    elseif ($argv[$i] == "-f" OR $argv[$i] == "-file" AND isset($argv[$i+1])) { $strFilename=$argv[$i+1];}

		// If debug (-v, -verbose)
		elseif ($argv[$i] == "-v" OR $argv[$i] == "-verbose" AND isset($argv[$i+1])) { $verbose_level=$argv[$i+1];}
	}

	// Set variable defaults, if needed
	if (!isset($verbose_level)){$verbose_level=1;}
	if (!isset($curl_timeout)){$curl_timeout=10;}

	if (isset($strBulk) AND isset($strURL)) {
		echo "ERROR: Cannot set both -u and -b. Use -help for details.\n";
		exit(3);
	} else if (isset($strURL)){
		// Execute an individual URL check and display the results
		$site_array = get_category($strURL,$curl_timeout);
	} else if (isset($strBulk)){
		$site_array = get_category_bulk($strBulk,$curl_timeout);
	}
?>