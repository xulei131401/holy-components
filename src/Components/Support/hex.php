<?php 
//php各种进制转换总结

//1.二进制转十进制
echo "二进制转十进制：".bindec(1000)."<br>";

//2.二进制转十六进制
echo "二进制转十六进制：".bin2hex(1000)."<br>";

//3.十进制转二进制
echo "十进制转二进制：".decbin(4)."<br>";

//4.十进制转八进制
echo "十进制转八进制：".decoct(4)."<br>";

//5.十进制转十六进制
echo "十进制转十六进制: ".dechex(16)."<br>";

//6.八进制转十进制
echo "八进制转十进制：".octdec(7)."<br>";

//7.十六进制转十进制
echo "十六进制转十进制：".hexdec('A37334')."<br>";

//8.任意进制转换
echo base_convert('A37334', 16, 2)."<br>";
//总结：高等进制的参数都当做字符串处理
//位运算
$currentTimes = 4;
//位运算会自动转换为十进制
$sleepTime = 1 << $currentTimes;