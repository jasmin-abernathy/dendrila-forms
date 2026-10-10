<?php
declare(strict_types=1);
require_once __DIR__.'/Core.php';
require_once __DIR__.'/Storage.php';
function df_submit(string $id,array $form): array {
 return df_update($id,static function(array $r) use($form): array {
  if ($r['status']!=='draft' || $r['cursor']!=='__review') throw new RuntimeException('Envoi impossible.');
  $answers=df_prune($form,$r['answers']);
  foreach (df_steps($form,$answers) as $q) df_answer($q,$answers[$q['id']]??null);
  $r['answers']=$answers; $r['status']='submitted'; $r['submitted_at']=gmdate('c');
  return $r;
 });
}
function df_review_back(string $id,array $form): array {
 return df_update($id,static function(array $r) use($form): array {
  if ($r['status']!=='draft' || $r['cursor']!=='__review') throw new RuntimeException('Retour impossible.');
  $steps=df_steps($form,$r['answers']); $r['cursor']=end($steps)['id']; return $r;
 });
}
