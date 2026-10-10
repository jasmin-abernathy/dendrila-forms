<?php
declare(strict_types=1);
function df_form(string $slug): array {
    if (!preg_match('/\A[a-z0-9-]{1,60}\z/D',$slug)) throw new InvalidArgumentException('Formulaire inconnu.');
    $file=dirname(__DIR__).'/forms/'.$slug.'.json';
    if (!is_file($file)) throw new InvalidArgumentException('Formulaire inconnu.');
    $form=json_decode((string)file_get_contents($file),true,512,JSON_THROW_ON_ERROR);
    if (!is_array($form) || !in_array($form['mode']??null,['survey','interview'],true)
        || !is_string($form['title']??null) || trim($form['title'])===''
        || !is_string($form['description']??null)
        || !is_array($form['sections']??null) || !$form['sections'])
        throw new InvalidArgumentException('Formulaire invalide.');
    $ids=[]; $types=[]; $options=[]; $count=0;
    foreach ($form['sections'] as $section) {
        if (!is_string($section['title']??null) || !is_array($section['questions']??null) || !$section['questions'])
            throw new InvalidArgumentException('Section invalide.');
        foreach ($section['questions'] as $q) {
            $id=$q['id']??'';
            if (!is_string($id) || !preg_match('/\A[a-z][a-z0-9_]{0,59}\z/D',$id)
                || isset($ids[$id]) || !is_string($q['label']??null)
                || !is_bool($q['required']??null)
                || !in_array($q['type']??null,['text','textarea','single','multiple','scale'],true))
                throw new InvalidArgumentException('Question invalide.');
            $c=$q['show_if']??null;
            if ($c!==null && (!is_array($c) || !is_string($c['question']??null)
                || !is_string($c['equals']??null)
                || ($types[$c['question']]??null)!=='single'
                || !in_array($c['equals'],$options[$c['question']]??[],true)))
                throw new InvalidArgumentException('Condition invalide.');
            if (in_array($q['type'],['single','multiple'],true)) {
                $values=[];
                if (!is_array($q['options']??null) || count($q['options'])<2) throw new InvalidArgumentException('Options manquantes.');
                foreach ($q['options'] as $o) {
                    if (!is_array($o) || !is_string($o['value']??null) || !is_string($o['label']??null) || trim($o['label'])===''
                        || !preg_match('/\A[a-z0-9_-]{1,50}\z/D',$o['value'])
                        || in_array($o['value'],$values,true)) throw new InvalidArgumentException('Option invalide.');
                    $values[]=$o['value'];
                }
                $options[$id]=$values;
            }
            if ($q['type']==='scale' && (!is_int($q['min']??null) || !is_int($q['max']??null)
                || $q['min']<0 || $q['max']>10 || $q['min']>=$q['max'])) throw new InvalidArgumentException('Échelle invalide.');
            if (++$count>80) throw new InvalidArgumentException('80 questions maximum.');
            $ids[$id]=true; $types[$id]=$q['type'];
        }
    }
    return $form;
}
function df_steps(array $form,array $answers): array {
    $list=[];
    foreach ($form['sections'] as $section) foreach ($section['questions'] as $q) {
        $c=$q['show_if']??null;
        if ($c && ($answers[$c['question']]??null)!==$c['equals']) continue;
        $q['section']=$section['title']; $list[]=$q;
    }
    return $list;
}
function df_prune(array $form,array $answers): array {
    return array_intersect_key($answers,array_flip(array_column(df_steps($form,$answers),'id')));
}
function df_answer(array $q,mixed $v,bool $required=true): mixed {
    if ($q['type']==='multiple') {
        if ($v===null || $v==='') $v=[];
        if (!is_array($v) || !array_is_list($v) || count($v)>20 || count($v)!==count(array_unique($v,SORT_REGULAR))) throw new InvalidArgumentException('Choix invalides.');
        foreach ($v as $x) if (!is_string($x) || !in_array($x,array_column($q['options'],'value'),true)) throw new InvalidArgumentException('Choix inconnu.');
        if ($required && $q['required'] && !$v) throw new InvalidArgumentException('Réponse obligatoire.');
        return array_values($v);
    }
    if ($q['type']==='scale') {
        if (!is_string($v) && !is_int($v) && $v!==null) throw new InvalidArgumentException('Note invalide.');
        if ($v==='' || $v===null) {
            if ($required && $q['required']) throw new InvalidArgumentException('Réponse obligatoire.');
            return '';
        }
        $n=filter_var($v,FILTER_VALIDATE_INT);
        if ($n===false || $n<$q['min'] || $n>$q['max']) throw new InvalidArgumentException('Note invalide.');
        return $n;
    }
    if (!is_string($v)) throw new InvalidArgumentException('Réponse invalide.');
    $v=trim(str_replace("\r",'',$v));
    if (strlen($v)>($q['type']==='textarea'?18000:1000)) throw new InvalidArgumentException('Réponse trop longue.');
    if ($required && $q['required'] && $v==='') throw new InvalidArgumentException('Réponse obligatoire.');
    if ($q['type']==='single' && $v!=='' && !in_array($v,array_column($q['options'],'value'),true)) throw new InvalidArgumentException('Choix inconnu.');
    return $v;
}
