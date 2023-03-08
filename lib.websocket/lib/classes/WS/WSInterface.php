<?php
namespace WS;

interface WSInterface { 
   public function onOpen($clientChat); 
   public function onClose($clientChat); 
   public function onMessage($clientChat, $receivedText); 
}  
