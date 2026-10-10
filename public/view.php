<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?=df_h($form['title']??'Dendrila Forms')?></title><link rel="stylesheet" href="style.css"></head>
<body><div class="shell">
<header><a class="brand" href="./">🌱 Dendrila Forms</a><span class="pill">Démonstration · v0.1</span></header>
<main>
<?php if ($error!==''): ?><p class="error" role="alert"><?=df_h($error)?></p><?php endif; ?>
<?php if (!$form && $slug!==''): ?>
<section class="panel"><h1>Formulaire introuvable</h1><a href="./">Retour</a></section>
<?php elseif (!$form): ?>
<div class="intro"><p class="eyebrow">Moteur de questionnaires libre</p><h1>Une question à la fois, à votre rythme.</h1>
<p class="lead">Essayez deux parcours fictifs. Ne saisissez aucune donnée personnelle ou sensible dans cette démonstration.</p></div>
<div class="cards">
<a class="card" href="?form=survey-demo"><span class="symbol">☑</span><h2>Enquête</h2><p>Choix, échelle, questions conditionnelles, progression.</p><strong>Essayer →</strong></a>
<a class="card" href="?form=interview-demo"><span class="symbol">✎</span><h2>Entretien</h2><p>Présentation, thèmes ordonnés, longues réponses et relecture.</p><strong>Essayer →</strong></a></div>
<?php else: ?>
<p class="back"><a href="./">← Tous les modèles</a></p>
<section class="panel">
<p class="eyebrow"><?=($form['mode']==='interview'?'Mode entretien':'Mode enquête')?></p>
<h1><?=df_h($form['title'])?></h1><p class="lead"><?=df_h($form['description'])?></p>
<?php if (!$record): ?>
<p class="notice">Les brouillons sont enregistrés sur le serveur, sans publication automatique. Conservez votre code de reprise secret.</p>
<form method="post"><input type="hidden" name="csrf" value="<?=df_h($_SESSION['csrf'])?>">
<button class="primary" name="action" value="start">Commencer</button></form>
<details><summary>Reprendre avec mon code</summary>
<form method="post" class="stack"><input type="hidden" name="csrf" value="<?=df_h($_SESSION['csrf'])?>">
<label for="code">Code de reprise</label><input id="code" name="code" required autocomplete="off" spellcheck="false">
<button class="secondary" name="action" value="resume">Reprendre</button></form></details>
<?php elseif ($record['status']==='submitted'): ?>
<div class="notice" role="status"><h2>Réponses enregistrées ✓</h2><p>Cet envoi est verrouillé. Il n'est ni publié ni envoyé par e-mail.</p></div>
<form method="post"><input type="hidden" name="csrf" value="<?=df_h($_SESSION['csrf'])?>">
<button class="danger" name="action" value="delete">Supprimer mes réponses</button></form>
<?php else: ?>
<div class="resume-key"><strong>Votre code de reprise</strong><code><?=df_h((string)$_SESSION['df_'.$slug])?></code>
<small>À conserver en lieu sûr : ce code permet de lire ou de supprimer vos réponses.</small></div>
<?php if ($review): ?>
<p class="eyebrow">Dernière étape</p><h2>Relire avant l'envoi</h2>
<?php foreach ($steps as $q): ?>
<div class="review"><small><?=df_h($q['section'])?></small><h3><?=df_h($q['label'])?></h3>
<p class="answer"><?=df_h(df_label($q,$record['answers'][$q['id']]??''))?:'—'?></p></div>
<?php endforeach; ?>
<div class="actions">
<form method="post"><input type="hidden" name="csrf" value="<?=df_h($_SESSION['csrf'])?>">
<button class="secondary" name="action" value="review_back">← Modifier</button></form>
<form method="post"><input type="hidden" name="csrf" value="<?=df_h($_SESSION['csrf'])?>">
<button class="primary" name="action" value="submit">Envoyer mes réponses</button></form></div>
<?php elseif ($question): ?>
<div class="step"><span><?=df_h($question['section'])?></span><strong>Question <?=($index+1)?> / <?=count($steps)?></strong></div>
<progress value="<?=($index+1)?>" max="<?=count($steps)?>" aria-label="Progression du questionnaire"></progress>
<h2><?=df_h($question['label'])?></h2>
<p class="hint"><?=($question['required']?'Question obligatoire':'Question facultative')?></p>
<?php if ($form['mode']==='interview' && isset($steps[$index+1])): ?>
<aside class="preview"><strong>Pour la suite</strong><p><?=df_h($steps[$index+1]['label'])?></p></aside>
<?php endif; ?>
<form method="post" class="stack">
<input type="hidden" name="csrf" value="<?=df_h($_SESSION['csrf'])?>">
<input type="hidden" name="question_id" value="<?=df_h($question['id'])?>">
<?php $saved=($error!=='' && ($_POST['question_id']??'')===$question['id'])?($_POST['answer']??''):($record['answers'][$question['id']]??''); ?>
<?php if ($question['type']==='textarea'): ?>
<label class="sr-only" for="answer">Votre réponse</label><textarea id="answer" name="answer" rows="8"><?=df_h(is_string($saved)?$saved:'')?></textarea>
<?php elseif ($question['type']==='text'): ?>
<label class="sr-only" for="answer">Votre réponse</label><input id="answer" name="answer" value="<?=df_h(is_string($saved)?$saved:'')?>">
<?php elseif ($question['type']==='scale'): ?>
<fieldset><legend class="sr-only">Choisissez une note</legend><div class="options scale">
<?php for ($n=$question['min'];$n<=$question['max'];$n++): ?><label class="option">
<input type="radio" name="answer" value="<?=$n?>" <?=(string)$saved===(string)$n?'checked':''?><span><?=$n?></span></label><?php endfor; ?>
</div></fieldset>
<?php else: ?>
<fieldset><legend class="sr-only">Choisissez votre réponse</legend><div class="options">
<?php foreach ($question['options'] as $opt): ?><label class="option">
<input type="<?=($question['type']==='multiple'?'checkbox':'radio')?>" name="<?=($question['type']==='multiple'?'answer[]':'answer')?>" value="<?=df_h($opt['value'])?>" <?=($question['type']==='multiple'?(is_array($saved)&&in_array($opt['value'],$saved,true)):$saved===$opt['value'])?'checked':''?>>
<span><?=df_h($opt['label'])?></span></label><?php endforeach; ?>
</div></fieldset>
<?php endif; ?>
<div class="actions">
<?php if ($index>0): ?><button class="secondary" formnovalidate name="action" value="back">← Précédent</button><?php endif; ?>
<button class="primary" name="action" value="next"><?=($index===count($steps)-1?'Relire les réponses':'Enregistrer et continuer →')?></button></div></form>
<?php endif; ?>
<div class="bottom"><form method="post"><input type="hidden" name="csrf" value="<?=df_h($_SESSION['csrf'])?>">
<button class="quiet" name="action" value="leave">Quitter sans supprimer</button></form>
<form method="post"><input type="hidden" name="csrf" value="<?=df_h($_SESSION['csrf'])?>">
<button class="quiet destructive" name="action" value="delete">Supprimer mes réponses</button></form></div>
<?php endif; ?>
</section>
<?php endif; ?>
</main><footer>Projet libre · Aucun traceur tiers · Aucune publication automatique</footer>
</div></body></html>
