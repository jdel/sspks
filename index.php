<?php
/*
error_reporting(E_ALL);
ini_set('display_errors', True);

example data passed by a syno

language = enu
timezone = Brussels
unique = synology_cedarview_412
arch = cedarview
major = 4
minor = 1
build = 2636
package_update_channel = stable

http://hackst.com/#X0j4l
*/

$spkDir = "packages/";	// This has to be a directory relative to
			// where this  script is and served by Apache
$synologyModels = "conf/synology_models.conf";	// File where Syno models are
						// stored in "DS412+=cedarview"
						// type format
$siteName = "Simple SPK Server";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
	$language = trim($_POST['language']);
	$timezone = trim($_POST['timezone']);
	$arch = trim($_POST['arch']);
	$major = trim($_POST['major']);
	$minor = trim($_POST['minor']);
	$build = trim($_POST['build']);
	$channel = trim($_POST['package_update_channel']);

	if (!$language || !$timezone || !$arch || !$major || !$minor || !$build || !$channel
		 || $_SERVER['HTTP_USER_AGENT'] != "\"Mozilla/4.0 (compatible; MSIE 6.1; Windows XP)\"" ){
		header('Content-type: text/html');
		header('HTTP/1.1 404 Not Found');
		header('Status: 404 Not Found');
	} else {
		echo stripslashes(json_encode(DisplayPackagesJSON(GetPackageList($spkDir, $arch, $channel, $major.".".$minor.".".$build))));
//		echo stripslashes(json_encode(DisplayPackagesJSON(GetPackageList($spkDir, $arch, $channel, $major.".".$minor.".".$build))));
	}
}
elseif($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$arch = trim($_GET['arch']);
	$channel = trim($_GET['channel']);
	$fullList = trim($_GET['fulllist']);
	$packagesAvailable = array();

	header('Content-type: text/html');
	echo "<!DOCTYPE html>\n";
	echo "<html>\n";
	echo "\t<head>\n";
	echo "\t\t<title>".$siteName."</title>\n";
	echo "\t\t<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />\n";
	echo "\t\t<script src=\"data/js/lib/prototype.js\" type=\"text/javascript\"></script>\n";
	echo "\t\t<script src=\"data/js/src/scriptaculous.js\" type=\"text/javascript\"></script>\n";
	echo "\t\t<link rel=\"stylesheet\" href=\"data/css/style.css\" type=\"text/css\" />\n";
	echo "\t\t<link rel=\"stylesheet\" href=\"data/css/style_mobile.css\" type=\"text/css\" media=\"handheld\"/>\n";
	echo "\t</head>\n";
	echo "\t<body>\n";
	echo "\t\t<h1>".$siteName."</h1>\n";
	echo "\t\t<div id=\"menu\">\n";
	echo "\t\t\t<ul>\n";
	echo "\t\t\t\t<li><a href=\".\">Synology Models</a></li>\n";
	echo ($arch && !$channel)?"\t\t\t\t<li><a href=\"".$_SERVER['REQUEST_URI']."&channel=beta\">Show Beta Packages</a></li>\n":"";
	echo $channel?"\t\t\t\t<li><a href=\"index.php?arch=".$arch."\">Hide Beta Packages</a></li>\n":"";
	echo !$fullList?"\t\t\t\t<li><a href=\"index.php?fulllist=true\">Full Packages List</a></li>\n":"";
	echo "\t\t\t\t<li class=\"last\"><a href=\"http://github.com/jdel/sspks\">Download</a></li>\n";
	echo "\t\t\t</ul>\n";
	echo "\t\t</div>\n";
	echo "\t\t<ul ".($arch?"class=\"fancy\"":"").">\n";
	if ($arch){
		DisplayPackagesHTML(GetPackageList($spkDir, $arch, $channel, "skip"));
	} elseif ($fullList) {
		DisplayAllPackages($spkDir);
	} else {
		DisplaySynoModels($synologyModels);
	}
	echo "\t\t</ul>\n";
	echo "\t\t<hr />\n";
	echo "\t\t<div id=\"footer\">\n";
	echo "\t\t\tHelp this server get better on <a href=\"http://github.com/jdel/sspks\">Github</a>\n";
	echo "\t\t</div>\n";
	echo "\t</body>\n";
	echo "</html>";
}
else
{
	header('Content-type: text/html');
	header('HTTP/1.1 404 Not Found');
	header('Status: 404 Not Found');
}

function GetPackageList($spkDir="packages/", $arch="noarch", $beta=false, $version="") {
    $host = $_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/"))."/";
	$packagesList = getDirectoryList($spkDir, ".*\.nfo");
	$packagesAvailable = array();
	if (!empty($packagesList)){
		foreach($packagesList as $nfoFile){
			$packageInfo = array();
			$spkFile = basename($nfoFile, ".nfo").".spk";
			$thumb_72 = basename($nfoFile, ".nfo")."_thumb_72.png";
			$thumb_120 = basename($nfoFile, ".nfo")."_thumb_120.png";
			if(file_exists($spkDir.$nfoFile) && file_exists($spkDir.$spkFile)){
				$fileHandle = fopen($spkDir.$nfoFile, 'r');
				while(!feof($fileHandle))
				{
			                $line = explode("=", chop(str_replace("\"", "", fgets($fileHandle))));
			                if (trim($line[0])){ $packageInfo[$line[0]] = $line[1]; }
				}
		        fclose($fileHandle);
				$packageInfo['nfo'] = $spkDir.$nfoFile;
				$packageInfo['spk'] = $spkDir.$spkFile;
				if(file_exists($spkDir.$thumb_72)){
				    $packageInfo['thumbnail'][] = "http://".$host.$spkDir.$thumb_72;
				} else {
				    $packageInfo['thumbnail'][] = "http://".$host.$spkDir."default_package_icon_72.png";
				}
				if(file_exists($spkDir.$thumb_120)){
				    $packageInfo['thumbnail'][] = "http://".$host.$spkDir.$thumb_120;
				} else {
				    $packageInfo['thumbnail'][] = "http://".$host.$spkDir."default_package_icon_120.png";
				}
                foreach(getDirectoryList($spkDir, basename($nfoFile, ".nfo").".*_screen_.*\.png") as $snapshot){
                    $packageInfo['snapshot'][] = "http://".$host.$spkDir.$snapshot;
                }
				if (	(empty($packagesAvailable[$packageInfo['package']])
					|| version_compare($packageInfo['version'], $packagesAvailable[$packageInfo['package']]['version'], ">"))
					&& ($packageInfo['arch'] == $arch || $packageInfo['arch'] == "noarch")
					&& (($beta == "beta" && $packageInfo['beta'] == true) || empty($packageInfo['beta']))
					&& ((version_compare($version, $packageInfo['firmware'], ">=")) || $version == "skip")
					) {
					$packagesAvailable[$packageInfo['package']] = $packageInfo;
				}
			}
		}
	}
	return $packagesAvailable;
}

function DisplaySynoModels($synologyModelsFile) {
	if(file_exists($synologyModelsFile)){
		$synologyModels = array();
                $fileHandle = fopen($synologyModelsFile, 'r');
                while(!feof($fileHandle))
		{
			$line = explode("=", chop(str_replace("\"", "", fgets($fileHandle))));
			if ($line[0]){ $synologyModels[$line[0]] = $line[1]; }
		}
                fclose($fileHandle);
		ksort($synologyModels);
		foreach ($synologyModels as $synoName => $synoArch){
			echo "\t\t\t<li><a href=\"?arch=".$synoArch."\">".$synoName."</a></li>\n";
		}
        }
        else
        {
		echo "\t\t\t<li>Couldn't find Synology models</li>";
        }
}

function DisplayPackagesHTML($packagesAvailable){
	$host = $_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/"))."/";
	foreach($packagesAvailable as $packageInfo){
		echo "\t\t\t<li class=\"package\">\n";
		echo "\t\t\t\t<div class=\"spk_icon\">\n";
		echo "\t\t\t\t\t<a href=\"http://".$host.$packageInfo['spk']."\"><img src=\"".$packageInfo['thumbnail'][0]."\" alt=\"".$packageInfo["displayname"]."\" />".($packageInfo['beta']?"<ins></ins>":"")."</a>\n";
		echo "\t\t\t\t</div>\n";
		echo "\t\t\t\t<div class=\"spk_desc\">\n";
		echo "\t\t\t\t\t<span class=\"sub1\">".$packageInfo["displayname"]." v".$packageInfo["version"]."</span><br />\n";
		echo "\t\t\t\t\t<p>".$packageInfo["description"];
		echo " <a id=\"".$packageInfo['package']."_show\" href=\"#nogo\" onclick=\"Effect.toggle('".$packageInfo['package']."_detail', 'blind', { duration: 0.5 }); Effect.toggle('".$packageInfo['package']."_show', 'appear', { duration: 0.3 }); Effect.toggle('".$packageInfo['package']."_hide', 'appear', { duration: 0.3, delay: 0.5 }); return false;\">More...</a>";
		echo " <a id=\"".$packageInfo['package']."_hide\" href=\"#nogo\" onclick=\"Effect.toggle('".$packageInfo['package']."_detail', 'blind', { duration: 0.5 }); Effect.toggle('".$packageInfo['package']."_hide', 'appear', { duration: 0.3 }); Effect.toggle('".$packageInfo['package']."_show', 'appear', { duration: 0.3, delay: 0.5 }); return false;\" style=\"display: none;\">Hide</a>\n";
		echo "\t\t\t\t\t</p>\n";
		echo "\t\t\t\t\t<div style=\"display: none;\" id=\"".$packageInfo['package']."_detail\">\n";
		echo "\t\t\t\t\t<table>\n";
		echo "\t\t\t\t\t\t<tr><td>Package</td><td>".$packageInfo["package"]."</td></tr>\n";
		echo "\t\t\t\t\t\t<tr><td>Version</td><td>".$packageInfo["version"]."</td></tr>\n";
		echo "\t\t\t\t\t\t<tr><td>Display Name</td><td>".$packageInfo["displayname"]."</td></tr>\n";
		echo "\t\t\t\t\t\t<tr><td>Maintainer</td><td>".$packageInfo["maintainer"]."</td></tr>\n";
		echo "\t\t\t\t\t\t<tr><td>Arch</td><td>".$packageInfo["arch"]."</td></tr>\n";
		echo "\t\t\t\t\t\t<tr><td>Firmware</td><td>".$packageInfo["firmware"]."</td></tr>\n";
		echo "\t\t\t\t\t</table>\n";
		echo "\t\t\t\t\t</div>\n";
		echo "\t\t\t\t</div>\n";
		echo "\t\t\t</li>\n";
	}
}

function DisplayPackagesJSON($packagesAvailable){
	$packagesJSON = array();
	$host = $_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/"))."/";
	foreach($packagesAvailable as $packageInfo){
		$packageJson = array(
		"package" => $packageInfo["package"],
		"version" => $packageInfo["version"],
		"dname" => $packageInfo["displayname"],
		"desc" => $packageInfo["description"],
		"link" => "http://".$host.$packageInfo['spk'],
		"md5" => md5_file($packageInfo['spk']),
		"size" => filesize($packageInfo['spk']),
		"qinst" => !empty($packageInfo['qinst'])?$packageInfo['qinst']:false,					// quick install
		"qstart" => !empty($packageInfo['start'])?$packageInfo['start']:false,					// quick start
		"depsers" => !empty($packageInfo['start_dep_services'])?$packageInfo['start_dep_services']:"",		// required started packages
		"deppkgs" => !empty($packageInfo['install_dep_services'])?$packageInfo['install_dep_services']:"",	// required installed packages
		"maintainer" => $packageInfo["maintainer"],
		"changelog" => !empty($packageInfo["changelog"])?$packageInfo["changelog"]:"",
		"beta" => !empty($packageInfo['beta'])?$packageInfo['beta']:false,					// beta channel
		//"icon" => $packageInfo['package_icon']
		//"icon" => base64_encode(file_get_contents($packageInfo['png']))
		"thumbnail" => $packageInfo['thumbnail'],
//		"category" => 2,
		"download_count" => 6000,
		"price" => 0,
//		"recent_download_count" => 1222,
		"type" => 0,
		"snapshot" => $packageInfo['snapshot']
		);
		array_push($packagesJSON, $packageJson);
	}
	return $packagesJSON;
}

function DisplayAllPackages($spkDir) {
        $packagesList = getDirectoryList($spkDir, ".*\.spk");
	$host = $_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], "/"))."/";
        foreach($packagesList as $spkFile){
                echo "\t\t\t<li><a href=\"http://".$host.$spkDir.$spkFile."\">".$spkFile."</a></li>\n";
        }
}

function getDirectoryList ($directory, $filter){
	$results = array();
	$handler = opendir($directory);
	while ($file = readdir($handler)) {
		if ($file != "." && $file != ".." && preg_match("/".$filter."/", $file)) {
		      $results[] = $file;
		}
	}
	closedir($handler);
	sort($results);
	return $results;
}
?>
