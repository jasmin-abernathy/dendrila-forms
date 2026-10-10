<?php
require_once __DIR__.'/Flow.php';
function df_move(string $id,array $form,string $dir,string $qid,mixed $answer): array {
 return df_update($id,function($r) use($form,$dir,$qid,$answer) {
  if ($r['status']!=='draft') throw new RuntimeException('Terminé');
  $steps=df_steps($form,$r['answers']);
  $ids=array_column($steps,'id');
  $idx=array_search($qid,$ids,true);
  if ($idx===false || ($r['cursor']??$ids[0])!==$qid) throw new RuntimeException('Étape incorrecte');
  $r['answers'][$qid]=df_answer($steps[$idx],$answer,$dir!=='back');
  $r['answers']=df_prune($form,$r['answers']);
  $newIds=array_column(df_steps($form,$r['answers']),'id');
  $idx=array_search($qid,$newIds,true);
  if ($dir==='back') $r['cursor']=$newIds[max(0,$idx-1)];
  elseif ($dir==='next') $r['cursor']=$newIds[$idx+1]??'__review';
  else throw new RuntimeException('Action incorrecte');
  return $r;
 });
}
