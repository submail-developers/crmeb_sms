<?php
namespace app\admin\controller\sms;

use app\admin\controller\AuthController;
use crmeb\services\FormBuilder;
use crmeb\services\JsonService;
use crmeb\services\SMSService;
use crmeb\services\UtilService;
use think\facade\Route;
use app\admin\model\routine\RoutineTemplate as RoutineModel;
/**
 * 短信模板申请
 * Class SmsTemplateApply
 * @package app\admin\controller\sms
 */
class SmsTemplateApply extends AuthController
{
    /**
     * 显示资源列表
     *
     * @return string
     */
    public function index()
    {
//        $sms = new SMSService();
//        if(!$sms::$status) return $this->failed('请先填写短信配置');
        return $this->fetch();
    }

    /**
     * 异步获取模板列表
     */
    public function lst()
    {
        //重新获取模板
        $tem = new RoutineModel();
        $lst = $tem->where('status',0)->select()->toArray();
        foreach($lst as $k=>$v) {
            $lst[$k]['id']= $v['id'];
            $lst[$k]['templateid']= $v['tempid'];
            $lst[$k]['title']= $v['name'];
            $lst[$k]['mark']= "";
            $lst[$k]['type']= $v['tempkey'];
            $lst[$k]['status']= 1;
            $lst[$k]['content']= $v['content'];
            $lst[$k]['add_time']= date( "Y-m-d H:i", $v['add_time']);
        }
        return JsonService::successlayui('',$lst);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return string
     * @throws \FormBuilder\exception\FormBuilderException
     */
    public function create()
    {
        $field = [
            FormBuilder::input('title','模板名称'),
            FormBuilder::textarea('text','模板内容示例','【您的短信签名】您的验证码是：{$code}，有效期为{$time}分钟。如非本人操作，可不用理会。模板中的{$code}和{$time}需要替换成对应的变量，请开发者知晓。修改此项无效！'),
            FormBuilder::input('content','模板内容')->type('textarea'),
            FormBuilder::input('number','模板id'),
            FormBuilder::radio('type','模板类型',1)->options([['label'=>'验证码','value'=>1],['label'=>'通知','value'=>2],['label'=>'推广','value'=>3]])
        ];

        $form = FormBuilder::make_post_form('申请短信模板',$field,Route::buildUrl('save'),2);

        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存新建的资源
     */
    public function save()
    {
        $tem = [];
        $data = UtilService::postMore([
            ['title',''],
            ['content',''],
            ['type',0],
            ['number','']
        ]);
         //组装数据
        $tem = array(
            'tempkey'=>$data['type'],
            'name'=>$data['title'],
            'content'=>$data['content'],
            'tempid'=>$data['number'],
            'add_time'=>time()
        );
        if(!strlen(trim($data['title']))) return JsonService::fail('请输入模板名称');
        if(!strlen(trim($data['content']))) return JsonService::fail('请输入模板内容');
        if(!strlen(trim($data['number']))) return JsonService::fail('请输入模板id');
        $id = RoutineModel::insert($tem);
        if ($id) return JsonService::success('申请成功');
    }
}
