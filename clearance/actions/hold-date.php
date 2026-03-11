<?php
$last_day=$_POST['last_day'];
$ym=date('Y-m',strtotime($last_day));

if(date("d",strtotime($last_day))==11){
	echo date("F 26, Y",strtotime($last_day." -1 months"))." - ".date("F 10, Y",strtotime($last_day))." and ".date("F d, Y",strtotime($last_day));
}else if(date("d",strtotime($last_day))==26){
	echo date("F 11, Y",strtotime($last_day))." - ".date("F 25, Y",strtotime($last_day))." and ".date("F d, Y",strtotime($last_day));
}else if(date("d",strtotime($last_day))<26 && date("d",strtotime($last_day))>11){
	echo date("F 26, Y",strtotime($last_day." -1 months"))." - ".date("F 10, Y",strtotime($last_day))." and ".date("F 11, Y",strtotime($last_day))." - ".date("F d, Y",strtotime($last_day));
}else if(date("d",strtotime($last_day))>26){
	echo date("F 11, Y",strtotime($last_day))." - ".date("F 25, Y",strtotime($last_day))." and ".date("F 26, Y",strtotime($last_day))." - ".date("F d, Y",strtotime($last_day));
}else if(date("d",strtotime($last_day))<11){
	echo date("F 11, Y",strtotime($last_day." -1 months"))." - ".date("F 25, Y",strtotime($last_day." -1 months"))." and ".date("F 26, Y",strtotime($last_day." -1 months"))." - ".date("F d, Y",strtotime($last_day));
}