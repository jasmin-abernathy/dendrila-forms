<?php
declare(strict_types=1);
require dirname(__DIR__).'/src/Navigation.php';
require dirname(__DIR__).'/src/Throttle.php';
ini_set('session.gc_maxlifetime','604800');
session_set_cookie_params(['lifetime'=>604800,'path'=>'/','secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off','httponly'=>true,'samesite'=>'Lax']);
session_start();
if (!isset($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32));
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: private, no-store');
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'none'; style-src 'self'; form-action 'self'; base-uri 'none'; frame-ancestors 'none'");
function df_h(string $v): string { return htmlspecialchars($v,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }
function df_redirect(string $slug): never { header('Location: ?form='.rawurlencode($slug),true,303); exit; }
function df_label(array $q,mixed $v): string {
 if (is_array($v)) {
  $names=array_column($q['options']??[],'label','value');
  return implode(', ',array_map(static fn(string $x): string=>$names[$x]??$x,$v));
 }
 if ($q['type']==='single') {
  $names=array_column($q['options'],'label','value');
  return $names[(string)$v]??'';
 }
 return (string)$v;
}
$slug=is_string($_GET['form']??null)?$_GET['form']:(isset($_GET['form'])?'invalide':''); $form=null; $record=null; $error='';
try {
 if ($slug!=='') {
  $form=df_form($slug); $key='df_'.$slug;
  $token=(string)($_SESSION[$key]??'');
  $record=$token!==''?df_open($token,$slug):null;
  if ($record===null) unset($_SESSION[$key]);
  if ($_SERVER['REQUEST_METHOD']==='POST') {
   $posted=$_POST['csrf']??null;
   if (!is_string($posted) || $posted==='' || !hash_equals($_SESSION['csrf'],$posted))
    throw new RuntimeException('Session expirée : rechargez la page.');
   $action=is_string($_POST['action']??null)?$_POST['action']:'';
   if ($action==='start') {
    df_throttle('start',8,600);
    session_regenerate_id(true); $_SESSION[$key]=df_start($slug); df_redirect($slug);
   }
   if ($action==='resume') {
    $candidate=is_string($_POST['code']??null)?trim($_POST['code']):'';
    df_throttle('resume',24,600);
    if (!df_open($candidate,$slug)) throw new InvalidArgumentException('Code de reprise inconnu.');
    session_regenerate_id(true); $_SESSION[$key]=$candidate; df_redirect($slug);
   }
   if ($action==='leave') { unset($_SESSION[$key]); df_redirect($slug); }
   if (!$record) throw new RuntimeException('Commencez ou reprenez le formulaire.');
   if ($action==='delete') {
    df_delete($record['id']); unset($_SESSION[$key]); df_redirect($slug);
   }
   if ($action==='submit') { df_submit($record['id'],$form); df_redirect($slug); }
   if ($action==='review_back') { df_review_back($record['id'],$form); df_redirect($slug); }
   if (in_array($action,['next','back'],true)) {
    $qid=is_string($_POST['question_id']??null)?$_POST['question_id']:'';
    $answer=$_POST['answer']??'';
    df_move($record['id'],$form,$action,$qid,$answer);
    df_redirect($slug);
   }
   throw new InvalidArgumentException('Action inconnue.');
  }
 }
} catch (InvalidArgumentException|RuntimeException|JsonException $e) { $error=$e->getMessage(); }
if ($slug!=='' && !$form) http_response_code(404);
$steps=($form && $record)?df_steps($form,$record['answers']):[];
$current=$record?($record['cursor']??($steps[0]['id']??'')):'';
$index=array_search($current,array_column($steps,'id'),true);
$question=$index!==false?$steps[$index]:null;
$review=$record && $record['status']==='draft' && $record['cursor']==='__review';
require __DIR__.'/view.php';
