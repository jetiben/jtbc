<?php
namespace jtbc;
class ui extends page {
  public static function moduleActionInstall()
  {
    $status = 0;
    $message = '';
    $para = '1';
    $db_host = request::getPost('db_host');
    $db_username = request::getPost('db_username');
    $db_password = request::getPost('db_password');
    $db_database = request::getPost('db_database');
    $username = request::getPost('username');
    $password = request::getPost('password');
    $cpassword = request::getPost('cpassword');
    $email = request::getPost('email');
    if (SITESTATUS == 0)
    {
      if (base::isEmpty($db_host)) $message = tpl::take('install.text-tips-install-error-1', 'lng');
      else if (base::isEmpty($db_username)) $message = tpl::take('install.text-tips-install-error-2', 'lng');
      else if (base::isEmpty($db_database)) $message = tpl::take('install.text-tips-install-error-4', 'lng');
      else
      {
        $para = '2';
        if (base::isEmpty($username)) $message = tpl::take('install.text-tips-install-error-5', 'lng');
        else if (base::isEmpty($password)) $message = tpl::take('install.text-tips-install-error-6', 'lng');
        else if ($password != $cpassword) $message = tpl::take('install.text-tips-install-error-6c', 'lng');
        else if (!verify::isEmail($email)) $message = tpl::take('install.text-tips-install-error-7', 'lng');
        else
        {
          $para = '1';
          $db = new db();
          $db -> dbHost = $db_host;
          $db -> dbUsername = $db_username;
          $db -> dbPassword = $db_password;
          $db -> init();
          if ($db -> errStatus != 0)
          {
            $message = tpl::take('install.text-tips-install-error-8', 'lng');
          }
          else
          {
            $selectdb = false;
            $re = $db -> exec('use ' . $db_database . ';select database();');
            if (!is_numeric($re))
            {
              $re = $db -> exec('create database ' . $db_database);
              if ($re == true)
              {
                $re = $db -> exec('use ' . $db_database . ';select database();');
                if (is_numeric($re)) $selectdb = true;
              }
            }
            else $selectdb = true;
            if ($selectdb == true)
            {
              $para = '2';
              $mysql = file_get_contents('mysql.sql');
              $exec = $db -> exec($mysql);
              if (is_numeric($exec))
              {
                conn::$dbW = $db;
                $table = tpl::take(':/account:config.db_table', 'cfg');
                $prefix = tpl::take(':/account:config.db_prefix', 'cfg');
                $preset = array();
                $preset['username'] = $username;
                $preset['password'] = md5($password);
                $preset['email'] = $email;
                $preset['role'] = -1;
                $preset['lastip'] = request::getRemortIP();
                $preset['lasttime'] = base::getDateTime();
                $preset['time'] = base::getDateTime();
                $re = auto::autoInsertByVars($preset, $table, $prefix);
                if (is_numeric($re))
                {
                  $constPath = route::getActualRoute('common/incfiles/const.php');
                  $constContent = file_get_contents($constPath);
                  $constContent = str_replace('{$db_host}', $db_host, $constContent);
                  $constContent = str_replace('{$db_username}', $db_username, $constContent);
                  $constContent = str_replace('{$db_password}', $db_password, $constContent);
                  $constContent = str_replace('{$db_database}', $db_database, $constContent);
                  $constContent = str_replace('define(\'SITESTATUS\', 0)', 'define(\'SITESTATUS\', 100)', $constContent);
                  $constContentSave = file_put_contents($constPath, $constContent);
                  if ($constContentSave)
                  {
                    $indexPath = route::getActualRoute('index.php');
                    $indexContent = file_get_contents($indexPath);
                    $indexContent = str_replace('<?php if (SITESTATUS == 0) header(\'location: _install\');?>', '', $indexContent);
                    $fileBool1 = file_put_contents($indexPath, $indexContent);
                    $completePath = route::getActualRoute('complete.php');
                    $completeContent = '<?php' . chr(10);
                    $completeContent .= 'require_once(\'common/incfiles/jtbc.php\');' . chr(10);
                    $completeContent .= 'jtbc\\base::removeDir(\'_install\');' . chr(10);
                    $completeContent .= 'unlink(\'complete.php\');' . chr(10);
                    $completeContent .= 'header(\'location: ' . CONSOLEDIR . '\');' . chr(10);
                    $completeContent .= '?>' . chr(10);
                    $fileBool2 = file_put_contents($completePath, $completeContent);
                    if ($fileBool1 && $fileBool2)
                    {
                      $status = 1;
                      $para = route::getActualRoute('complete.php');
                    }
                    else $message = tpl::take('install.text-tips-install-error-10', 'lng');
                  }
                  else $message = tpl::take('install.text-tips-install-error-10', 'lng');
                }
                else $message = tpl::take('install.text-tips-install-error-9', 'lng');
              }
              else $message = tpl::take('install.text-tips-install-error-9', 'lng');
            }
            else $message = tpl::take('install.text-tips-install-error-8c', 'lng');
          }
        }
      }
    }
    $tmpstr = self::formatMsgResult($status, $message, $para);
    return $tmpstr;
  }
}
?>