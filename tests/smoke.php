<?php
declare(strict_types=1);
putenv('DENDRILA_DATA_DIR='.sys_get_temp_dir().'/dendrila-test-'.bin2hex(random_bytes(6)));
require dirname(__DIR__).'/src/Navigation.php';
require dirname(__DIR__).'/src/Throttle.php';
function check(bool $ok,string $label): void { if (!$ok) throw new RuntimeException('Échec : '.$label); }
$form=df_form('survey-demo');
check(df_validate_form($form)===$form,'validation en mémoire');
try { df_validate_form(['mode'=>'invalid']); check(false,'modèle erroné'); }
catch (InvalidArgumentException $e) {}
check(count(df_steps($form,[]))===3,'condition cachée');
check(count(df_steps($form,['frequency'=>'daily']))===4,'condition visible');
check(count(df_steps(df_form('interview-demo'),[]))===5,'questions entretien');
$optional=['id'=>'extras','type'=>'multiple','required'=>false,'options'=>[
    ['value'=>'a','label'=>'A'],['value'=>'b','label'=>'B']]];
check(df_answer($optional,'')===[],'case facultative sans sélection');
check(df_answer($optional,[])===[],'choix multiples vides');
try { df_answer($optional,['a','a']); check(false,'doublons acceptés'); }
catch (InvalidArgumentException $e) {}
try { df_answer($optional,['unknown']); check(false,'choix inconnu accepté'); }
catch (InvalidArgumentException $e) {}
$required=$optional; $required['required']=true;
try { df_answer($required,''); check(false,'réponse obligatoire omise'); }
catch (InvalidArgumentException $e) {}
$invalidForm=$form;
$invalidForm['sections'][0]['questions'][1]['show_if']=['question'=>'missing','equals'=>'x'];
$invalidPath=dirname(__DIR__).'/forms/invalid-test-fixture.json';
file_put_contents($invalidPath,json_encode($invalidForm,JSON_THROW_ON_ERROR));
try { df_form('invalid-test-fixture'); check(false,'condition invalide acceptée'); }
catch (InvalidArgumentException $e) {}
finally { unlink($invalidPath); }
$_SERVER['REMOTE_ADDR']='192.0.2.55';
df_throttle('test',2,600); df_throttle('test',2,600);
try { df_throttle('test',2,600); check(false,'limite non appliquée'); }
catch (RuntimeException $e) {}
unset($_SERVER['REMOTE_ADDR']);

$code=df_start('survey-demo'); $parts=explode('.',$code); $id=$parts[0];
check(df_open($code,'survey-demo')!==null,'reprise');
check(df_open($code,'interview-demo')===null,'isolation');
check(df_open($id.'.'.str_repeat('0',64),'survey-demo')===null,'code erroné');
try { df_move($id,$form,'next','frequency','autre'); check(false,'choix étranger'); }
catch (InvalidArgumentException $e) {}
$r=df_move($id,$form,'next','frequency','daily');
check($r['cursor']==='difficulty','affichage conditionnel');
$r=df_move($id,$form,'next','difficulty','3');
$r=df_move($id,$form,'back','features',[]);
$r=df_move($id,$form,'back','difficulty','3');
$r=df_move($id,$form,'next','frequency','never');
check($r['cursor']==='features' && !isset($r['answers']['difficulty']),'purge réponses masquées');
$r=df_move($id,$form,'next','features',['notes','tasks']);
$r=df_move($id,$form,'next','feedback','Une idée');
check($r['cursor']==='__review','relecture');
$r=df_submit($id,$form);
check($r['status']==='submitted','soumission');
try { df_move($id,$form,'back','feedback',''); check(false,'modification verrouillée'); }
catch (RuntimeException $e) {}
check(!str_contains((string)file_get_contents(df_path($id)),$parts[1]),'pas de secret en clair');
df_delete($id);
check(df_open($code,'survey-demo')===null,'suppression');
$_SERVER['DOCUMENT_ROOT']=df_storage();
try { df_storage(); check(false,'stockage public'); }
catch (RuntimeException $e) {}
unset($_SERVER['DOCUMENT_ROOT']);
echo "Dendrila Forms : tests OK\n";
