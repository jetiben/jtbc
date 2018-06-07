<?php
namespace jtbc;
class ui extends page {
  public static function moduleActionAdd()
  {
    $tmpstr = '';
    $status = 0;
    $message = '';
    $error = array();
    $name = request::getPost('name');
    $mobile = request::getPost('mobile');
    $email = request::getPost('email');
    $content = request::getPost('content');
    if (base::isEmpty($name)) array_push($error, tpl::take('api.text-tips-add-error-1', 'lng'));
    if (!verify::isMobile($mobile)) array_push($error, tpl::take('api.text-tips-add-error-2', 'lng'));
    if (!verify::isEmail($email)) array_push($error, tpl::take('api.text-tips-add-error-3', 'lng'));
    if (base::isEmpty($content)) array_push($error, tpl::take('api.text-tips-add-error-4', 'lng'));
    if (count($error) == 0)
    {
      $preset = array();
      $preset['dispose'] = 0;
      $preset['userip'] = request::getRemortIP();
      $preset['lang'] = self::getPara('lang');
      $preset['time'] = base::getDateTime();
      $re = auto::autoInsertByRequest($preset);
      if (is_numeric($re))
      {
        $status = 1;
        $message = tpl::take('api.text-tips-add-done', 'lng');
      }
      else array_push($error, tpl::take('api.text-tips-add-error-others', 'lng'));
    }
    if (!empty($error)) $message = implode('|', $error);
    $tmpstr = self::formatMsgResult($status, $message);
    return $tmpstr;
  }
}
?>