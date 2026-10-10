<?php
declare(strict_types=1);
putenv('DENDRILA_DATA_DIR='.sys_get_temp_dir().'/dendrila-test-'.bin2hex(random_bytes(6)));
require dirname(__DIR__).'/src/Navigation.php';
function check(bool $ok,string $label): void { if (!$ok) throw new RuntimeException('Échec : '.$label); }
$form=df_form('survey-demo');
check(count(df_steps($form,[]))===3,'condition cachée');
check(count(df_steps($form,['frequency'=>'daily']))===4,'condition visible');
check(count(df_steps(df_form('interview-demo'),[]))===5,'questions entretien');
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
