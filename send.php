<?php


print "Start";
require_once("SendSMS.php");

$sms_username = "";
$sms_password = "";
print "Sending SMS  ";


$destination = $_POST["rnum"];
$source = $_POST["snum"];
$message = $_POST["osms"];
print "$source";
$responses = send_sms($destination, $source, $message) or die ("Error: " . $errstr . "\n");
print " From: $source    ";


print " Message : $message   ID: ";
foreach ($responses as $response) {
echo "\t$response\n";
}
?>
<html>
	<style>
	body{
  background: #D3D3D3;
  padding-top: 10px;
} 

p{
  color: DarkRed; 
  font-family: "Courier";
  font-size: 20px;
  margin: 10px 0 0 10px;
  white-space: nowrap;
  overflow: hidden;
  width: 30em;
  animation: type 4s steps(60, end); 
}

p:nth-child(2){
  animation: type2 8s steps(60, end);
}

p a{
  color: DarkRed;
  text-decoration: none;
}
 

span{
  animation: blink 1s infinite;
}

@keyframes type{ 
  from { width: 0; } 
} 

@keyframes type2{
  0%{width: 0;}
  50%{width: 0;}
  100%{ width: 100; } 
} 

@keyframes blink{
  to{opacity: .0;}
}

::selection{
  background: black;
}
	</style>
<p> Message Sent ...  
	Created with ♥ By 
  <a href="https://fb.me/dzmanisso" title="Manisso">@Manisso</a> 
<span>|</span></p> 
</html>

