<?php
$ch = curl_init('http://localhost/api/login');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email'=>'nurse1@gmail.com','password'=>'12345678']));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$login = json_decode(curl_exec($ch), true);
$token = $login['token'] ?? '';
curl_close($ch);

$ch2 = curl_init('http://localhost/api/patients/10/latest-check');
curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$token]);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
echo curl_exec($ch2);
