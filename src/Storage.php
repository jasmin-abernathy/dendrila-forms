<?php
declare(strict_types=1);
function df_storage(): string {
    $path=getenv('DENDRILA_DATA_DIR')?:dirname(__DIR__).'/var';
    if (!str_starts_with($path,'/')) throw new RuntimeException('Chemin de données absolu requis.');
    if (!is_dir($path) && !mkdir($path,0700,true) && !is_dir($path)) throw new RuntimeException('Stockage indisponible.');
    $root=realpath($path);
    if ($root===false) throw new RuntimeException('Stockage inaccessible.');
    foreach ([realpath(dirname(__DIR__).'/public'),realpath((string)($_SERVER['DOCUMENT_ROOT']??''))] as $public)
        if ($public!==false && ($root===$public || str_starts_with($root,$public.DIRECTORY_SEPARATOR)))
            throw new RuntimeException('Stockage sous la racine publique interdit.');
    return $root;
}
function df_path(string $id): string {
    if (!preg_match('/\A[a-f0-9]{32}\z/D',$id)) throw new InvalidArgumentException('ID invalide.');
    return df_storage().'/'.$id.'.json';
}
function df_read(string $id): ?array {
    $file=df_path($id);
    if (!is_file($file)) return null;
    $r=json_decode((string)file_get_contents($file),true,512,JSON_THROW_ON_ERROR);
    if (!is_array($r) || ($r['id']??null)!==$id) throw new RuntimeException('Données corrompues.');
    return $r;
}
function df_write(array $r): void {
    $file=df_path($r['id']); $tmp=$file.'.'.bin2hex(random_bytes(6)).'.tmp';
    if (file_put_contents($tmp,json_encode($r,JSON_THROW_ON_ERROR|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT),LOCK_EX)===false)
        throw new RuntimeException('Sauvegarde impossible.');
    chmod($tmp,0600);
    if (!rename($tmp,$file)) { @unlink($tmp); throw new RuntimeException('Sauvegarde impossible.'); }
}
function df_start(string $slug): string {
    df_form($slug);
    $id=bin2hex(random_bytes(16)); $secret=bin2hex(random_bytes(32));
    $r=['id'=>$id,'form_id'=>$slug,'secret_hash'=>hash('sha256',$secret),
        'status'=>'draft','cursor'=>null,'answers'=>[],'created_at'=>gmdate('c')];
    $file=df_path($id); $fp=fopen($file,'x');
    if (!$fp) throw new RuntimeException('Création impossible.');
    chmod($file,0600);
    try { if (fwrite($fp,json_encode($r,JSON_THROW_ON_ERROR))===false) throw new RuntimeException('Écriture impossible.'); }
    finally { fclose($fp); }
    return $id.'.'.$secret;
}
function df_open(string $token,string $slug): ?array {
    if (!preg_match('/\A([a-f0-9]{32})\.([a-f0-9]{64})\z/D',$token,$parts)) return null;
    $r=df_read($parts[1]);
    if (!$r || $r['form_id']!==$slug || !hash_equals((string)$r['secret_hash'],hash('sha256',$parts[2]))) return null;
    return $r;
}
function df_update(string $id,callable $f): array {
    $lock=df_storage().'/'.$id.'.lock';
    if (!preg_match('/\A[a-f0-9]{32}\z/D',$id)) throw new InvalidArgumentException('ID invalide.');
    $fp=fopen($lock,'c');
    if (!$fp) throw new RuntimeException('Verrou indisponible.');
    chmod($lock,0600);
    try {
        if (!flock($fp,LOCK_EX)) throw new RuntimeException('Verrou indisponible.');
        $old=df_read($id); if (!$old) throw new RuntimeException('Réponses introuvables.');
        $new=$f($old);
        if (!is_array($new) || $new['id']!==$id || $new['form_id']!==$old['form_id'])
            throw new RuntimeException('Modification refusée.');
        df_write($new); return $new;
    } finally { flock($fp,LOCK_UN); fclose($fp); }
}
function df_delete(string $id): void {
    if (!preg_match('/\A[a-f0-9]{32}\z/D',$id)) throw new InvalidArgumentException('ID invalide.');
    $fp=fopen(df_storage().'/'.$id.'.lock','c');
    if (!$fp) throw new RuntimeException('Suppression impossible.');
    try {
        if (!flock($fp,LOCK_EX)) throw new RuntimeException('Verrou indisponible.');
        $file=df_path($id);
        if (is_file($file) && !unlink($file)) throw new RuntimeException('Suppression impossible.');
    } finally { flock($fp,LOCK_UN); fclose($fp); }
}
