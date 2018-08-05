<?php
error_reporting(0);
set_time_limit(0);
date_default_timezone_set('Asia/Tokyo');
$v_date = date("dmy");
$banner = '
------------------------------------------
        Yahoo Availability Checker
------------------------------------------
';
print $banner;
echo "-> Mailist  : ";
$mailist = rtrim(fgets(STDIN));
echo "-> Socks5 list  : ";
$sockss = rtrim(fgets(STDIN));
echo "-> Delay/sec : ";
$delay = rtrim(fgets(STDIN));
echo "-> Email/sec : ";
$check = rtrim(fgets(STDIN));
$emails   = file_get_contents($mailist);
$socks = file_get_contents($sockss);

function save($filename, $email)
{
    $save = fopen($filename, "a");
    fputs($save, "$email");
    fclose($save);
}

if($emails)
{
    $email = explode("\n", $emails);
    $count = count($email);
}
else
{
    die("Your mailist doesn't exist");
}

if($socks)
{
    $sock = explode("\n", $socks);
}
else
{
    echo "Your socks doesn't exist, so we not use socks";
}

$i = 0;
$no = 1;
while($i<$count)
{
    $q = explode("|", $email[$i]);

    $email[$i] = trim($q[0]);
    $pass = $q[1];
    $phone = $q[2];
        
    $mailcount = $count + 1;
    if($delay != 0 || !empty($check))
    {
        if($i%$check == 0)
        {
            print "\n--------------------Delay For $delay Seconds--------------------";
            sleep($delay);
        }
    }   
    echo "\n $no | $mailcount | ".date("G:i:s")." | ";
    
    $url = file_get_contents("http://localhost/yahoo/yahoo.php?email=".$email[$i]."&socks=".@$sock[$i]);
    $json = json_decode($url);
    
    if($json->status == "live")
    {  
        $msg  = "Live";
        $filename = "Live list - Yahoo Email.txt";
        save($filename, "live - ".trim($email[$i])."|".trim($pass)."|".trim($phone)."\n");
    }
    else
    {
        $msg  = "Die";
        $filename = "Die list - Yahoo Email.txt";
        save($filename, "die - ".$email[$i]."\n");
    }
    echo '' . $msg . ' => ' . $email[$i] . '';
    $i++;
    $no++;
}

echo "\n--------------------DONE!!!--------------------";
?>