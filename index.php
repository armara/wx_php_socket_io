<?php
require_once __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
use PHPSocketIO\SocketIO;

$port = '3773';
$io = new SocketIO($port);

$io->on('connection', function ($socket) use ($io) {
  echo ".\n\r.\n\r.\n\r";
  echo ">++ $socket->id --- new user has connected \n";
  $socket->addedUser = false;
  $socket->connectedUser = false;

  // var_dump($socket->username);

  $socket->on('connect confirm', function ($confirmArray) use ($socket) {
    global $users;
    $connectId = $confirmArray['connectId'];
    $socket->connectedUserConnectId = $connectId; // store in session current connection
    $users[$connectId] = $confirmArray;
    $socket->connectedUser = true;
    echo ('confirmArray--' . json_encode($confirmArray) . "\n");
    echo ("--users: confirm--\n" . json_encode($users, JSON_PRETTY_PRINT) . "\n");
  });

  $socket->on('add user', function($userdata) use($socket){
    global $users;
    $connectId = $userdata['connectId'];
    $socket->addedUserConnectId = $connectId; // store in session current addition
    $users[$connectId] = $userdata;
    $socket->addedUser = true;
    // here $socket->broadcast->emit usersList
    echo ("userdata--\n" . json_encode($userdata, JSON_PRETTY_PRINT) . "\n");
    echo ("--users: add--\n" . json_encode($users, JSON_PRETTY_PRINT) . "\n");
  });

  $socket->on('new message', function ($msg) use ($io) {
    $io->emit('new message', $msg);
  });

  $socket->on('disconnect', function() use($socket) {
    global $users;
    if($socket->addedUser) {
      unset($users[$socket->addedUserConnectId]);
      // here $socket->broadcast->emit usersList
      echo ("--users: left--\n" . json_encode($users, JSON_PRETTY_PRINT) . "\n");
    }

    if($socket->connectedUser) {
      unset($users[$socket->connectedUserConnectId]);
      echo ("--users: left without adding--\n" . json_encode($users, JSON_PRETTY_PRINT) . "\n");
    }
  });
});

Worker::runAll();
