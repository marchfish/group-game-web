<?php
namespace App\Services\mail;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EmailManager
{
    private $mail;

    function __construct() {
        $this->mail=new PhpMailer();
    }

    public function sendEmail($data = [])
    {
        $this->mail->IsSMTP(); // 启用SMTP
        $this->mail->Host = 'smtp.163.com'; //SMTP服务器 以126邮箱为例子
        $this->mail->SMTPSecure = "ssl";   // 设置安全验证方式为ssl
        $this->mail->Port = 994;  //邮件发送端口
        $this->mail->SMTPAuth = true;  //启用SMTP认证
        $this->mail->CharSet = "UTF-8"; //字符集
        $this->mail->Encoding = "base64"; //编码方式
        $this->mail->Username = '15736515576@163.com';  //你的邮箱
        $this->mail->Password = '';  //你的密码
        $this->mail->Subject = $data['title']; //邮件标题
        $this->mail->From = '15736515576@163.com';  //发件人地址（也就是你的邮箱）
        $this->mail->FromName = $data['name'];  //发件人姓名
        if ($data && is_array($data)) {
            $this->mail->AddAddress($data['address'], $data['user']); //添加收件人（地址，昵称）
            $this->mail->IsHTML(true); //支持html格式内容
            $this->mail->Body = $data['content']; //邮件主体内容
            //发送成功就删除
            if ($this->mail->Send()) {
                return true;
            } else {
                return "Mailer Error: " . $this->mail->ErrorInfo;// 错误信息
            }

        }
    }

    public function checkVerifyCode(string $email, string $code)
    {
        $row = DB::query()
            ->select(['*'])
            ->from('sys_email_code')
            ->where('email', '=', $email)
            ->limit(1)
            ->get()
            ->first()
        ;

        if (!$row || Carbon::now()->greaterThan($row->expired_at)) {
            throw new InvalidArgumentException('证码已过期，请重新获取', 400);
        }

        if ($row->code != $code) {
            throw new InvalidArgumentException('验证码错误', 400);
        }
    }
}
