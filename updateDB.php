<?php
	// check this file's MD5 to make sure it wasn't called before
	$prevMD5=@implode('', @file(dirname(__FILE__).'/setup.md5'));
	$thisMD5=md5(@implode('', @file("./updateDB.php")));
	if($thisMD5==$prevMD5){
		$setupAlreadyRun=true;
	}else{
		// set up tables
		if(!isset($silent)){
			$silent=true;
		}

		// set up tables
		setupTable('media', "create table if not exists `media` (   `id` INT unsigned not null auto_increment , primary key (`id`), `type` VARCHAR(10) not null , `title` VARCHAR(40) not null , `artist` VARCHAR(40) not null , `condition` VARCHAR(40) not null , `image` VARCHAR(40) , `image2` VARCHAR(40) , `price` DECIMAL(10,2) not null default '0.00' , `active` INT not null default '1' , `description` TEXT not null , `buttoncode` TEXT , `buttonactive` INT(11) default '1' , `owner` VARCHAR(25) , `ip` VARCHAR(16) , `date` DATE ) CHARSET utf8", $silent);
		setupTable('owners', "create table if not exists `owners` (   `id` INT unsigned not null auto_increment , primary key (`id`), `name` VARCHAR(40) , `pass` VARCHAR(40) , `level` INT(1) default '1' , `image` VARCHAR(40) ) CHARSET utf8", $silent);
		setupTable('ads', "create table if not exists `ads` (   `id` INT unsigned not null auto_increment , primary key (`id`), `advertiser` VARCHAR(40) not null , `adName` VARCHAR(40) , `image` VARCHAR(40) , `code` VARCHAR(40) , `text` VARCHAR(40) , `active` VARCHAR(40) not null , `StartDate` DATE , `EndDate` VARCHAR(40) , `catagory` INT unsigned ) CHARSET utf8", $silent, array( "ALTER TABLE `table3` RENAME `ads`","UPDATE `membership_userrecords` SET `tableName`='ads' where `tableName`='table3'","UPDATE `membership_userpermissions` SET `tableName`='ads' where `tableName`='table3'","UPDATE `membership_grouppermissions` SET `tableName`='ads' where `tableName`='table3'","ALTER TABLE ads ADD `field1` VARCHAR(40)","ALTER TABLE `ads` CHANGE `field1` `id` VARCHAR(40) ","ALTER TABLE `ads` CHANGE `id` `id` INT unsigned not null auto_increment ","ALTER TABLE ads ADD `field2` VARCHAR(40)","ALTER TABLE `ads` CHANGE `field2` `advertiser` VARCHAR(40) ","ALTER TABLE ads ADD `field3` VARCHAR(40)","ALTER TABLE `ads` CHANGE `field3` `adName` VARCHAR(40) ","ALTER TABLE ads ADD `field4` VARCHAR(40)","ALTER TABLE `ads` CHANGE `field4` `image` VARCHAR(40) ","ALTER TABLE `ads` CHANGE `photo` `photo` VARCHAR(40) ","ALTER TABLE ads ADD `field5` VARCHAR(40)","ALTER TABLE `ads` CHANGE `field5` `code` VARCHAR(40) ","ALTER TABLE ads ADD `field6` VARCHAR(40)","ALTER TABLE `ads` CHANGE `field6` `text` VARCHAR(40) ","ALTER TABLE ads ADD `field7` VARCHAR(40)","ALTER TABLE `ads` CHANGE `field7` `active` VARCHAR(40) ","ALTER TABLE ads ADD `field8` VARCHAR(40)","ALTER TABLE `ads` CHANGE `field8` `startDate` VARCHAR(40) ","ALTER TABLE ads ADD `field9` VARCHAR(40)","ALTER TABLE `ads` CHANGE `field9` `EndDate` VARCHAR(40) ","ALTER TABLE `ads` CHANGE `startDate` `StartDate` VARCHAR(40) ","ALTER TABLE ads ADD `field10` VARCHAR(40)","ALTER TABLE `ads` CHANGE `field10` `ShownOn` VARCHAR(40) "," ALTER TABLE `ads` CHANGE `StartDate` `StartDate` DATE "," ALTER TABLE `ads` CHANGE `advertiser` `advertiser` VARCHAR(40) not null "," ALTER TABLE `ads` CHANGE `active` `active` VARCHAR(40) not null ","ALTER TABLE `ads` CHANGE `ShownOn` `catagory` VARCHAR(40) "));
		setupTable('advertisers', "create table if not exists `advertisers` (   `id` INT unsigned not null auto_increment , primary key (`id`), `Name` VARCHAR(40) , `AffilateCode` VARCHAR(40) , `URL` VARCHAR(40) , `Contact` VARCHAR(40) , `email` VARCHAR(80) ) CHARSET utf8", $silent, array( "ALTER TABLE `table4` RENAME `advertisers`","UPDATE `membership_userrecords` SET `tableName`='advertisers' where `tableName`='table4'","UPDATE `membership_userpermissions` SET `tableName`='advertisers' where `tableName`='table4'","UPDATE `membership_grouppermissions` SET `tableName`='advertisers' where `tableName`='table4'","ALTER TABLE advertisers ADD `field1` VARCHAR(40)","ALTER TABLE `advertisers` CHANGE `field1` `id` VARCHAR(40) ","ALTER TABLE `advertisers` CHANGE `id` `id` INT unsigned not null auto_increment ","ALTER TABLE advertisers ADD `field2` VARCHAR(40)","ALTER TABLE `advertisers` CHANGE `field2` `Name` VARCHAR(40) ","ALTER TABLE advertisers ADD `field3` VARCHAR(40)","ALTER TABLE `advertisers` CHANGE `field3` `AffilateCode` VARCHAR(40) ","ALTER TABLE advertisers ADD `field4` VARCHAR(40)","ALTER TABLE `advertisers` CHANGE `field4` `URL` VARCHAR(40) ","ALTER TABLE advertisers ADD `field5` VARCHAR(40)","ALTER TABLE `advertisers` CHANGE `field5` `Contact` VARCHAR(40) ","ALTER TABLE advertisers ADD `field6` VARCHAR(40)","ALTER TABLE `advertisers` CHANGE `field6` `email` VARCHAR(40) ","ALTER TABLE `advertisers` CHANGE `email` `email` VARCHAR(80) "));


		// save MD5
		if($fp=@fopen(dirname(__FILE__).'/setup.md5', 'w')){
			fwrite($fp, $thisMD5);
			fclose($fp);
		}
	}


	function setupIndexes($tableName, $arrFields){
		if(!is_array($arrFields)){
			return false;
		}

		foreach($arrFields as $fieldName){
			if(!$res=@mysql_query("SHOW COLUMNS FROM `$tableName` like '$fieldName'")){
				continue;
			}
			if(!$row=@mysql_fetch_assoc($res)){
				continue;
			}
			if($row['Key']==''){
				@mysql_query("ALTER TABLE `$tableName` ADD INDEX `$fieldName` (`$fieldName`)");
			}
		}
	}


	function setupTable($tableName, $createSQL='', $silent=true, $arrAlter=''){
		global $Translation;
		ob_start();

		echo '<div style="padding: 5px; border-bottom:solid 1px silver; font-family: verdana, arial; font-size: 10px;">';

		// is there a table rename query?
		if(is_array($arrAlter)){
			$matches=array();
			if(preg_match("/ALTER TABLE `(.*)` RENAME `$tableName`/", $arrAlter[0], $matches)){
				$oldTableName=$matches[1];
			}
		}

		if($res=@mysql_query("select count(1) from `$tableName`")){ // table already exists
			if($row=@mysql_fetch_array($res)){
				echo str_replace("<TableName>", $tableName, str_replace("<NumRecords>", $row[0],$Translation["table exists"]));
				if(is_array($arrAlter)){
					echo '<br />';
					foreach($arrAlter as $alter){
						if($alter!=''){
							echo "$alter ... ";
							if(!@mysql_query($alter)){
								echo '<span class="label label-danger">' . $Translation['failed'] . '</span>';
								echo '<div class="text-danger">' . $Translation['mysql said'] . ' ' . mysql_error() . '</div>';
							}else{
								echo '<span class="label label-success">' . $Translation['ok'] . '</span>';
							}
						}
					}
				}else{
					echo $Translation["table uptodate"];
				}
			}else{
				echo str_replace("<TableName>", $tableName, $Translation["couldnt count"]);
			}
		}else{ // given tableName doesn't exist

			if($oldTableName!=''){ // if we have a table rename query
				if($ro=@mysql_query("select count(1) from `$oldTableName`")){ // if old table exists, rename it.
					$renameQuery=array_shift($arrAlter); // get and remove rename query

					echo "$renameQuery ... ";
					if(!@mysql_query($renameQuery)){
						echo '<span class="label label-danger">' . $Translation['failed'] . '</span>';
						echo '<div class="text-danger">' . $Translation['mysql said'] . ' ' . mysql_error() . '</div>';
					}else{
						echo '<span class="label label-success">' . $Translation['ok'] . '</span>';
					}

					if(is_array($arrAlter)) setupTable($tableName, $createSQL, false, $arrAlter); // execute Alter queries on renamed table ...
				}else{ // if old tableName doesn't exist (nor the new one since we're here), then just create the table.
					setupTable($tableName, $createSQL, false); // no Alter queries passed ...
				}
			}else{ // tableName doesn't exist and no rename, so just create the table
				echo str_replace("<TableName>", $tableName, $Translation["creating table"]);
				if(!@mysql_query($createSQL)){
					echo '<span class="label label-danger">' . $Translation['failed'] . '</span>';
					echo '<div class="text-danger">' . $Translation['mysql said'] . mysql_error() . '</div>';
				}else{
					echo '<span class="label label-success">' . $Translation['ok'] . '</span>';
				}
			}
		}

		echo "</div>";

		$out=ob_get_contents();
		ob_end_clean();
		if(!$silent){
			echo $out;
		}
	}
?>