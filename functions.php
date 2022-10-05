<?
//функция шифрования пароля (алгоритм blowfish)
function password_generation($password)
{
    return password_hash($password, PASSWORD_BCRYPT);
}
?>